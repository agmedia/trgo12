<?php

namespace App\Http\Controllers\Back\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Back\User\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    private const ROLE_LEVELS = [
        'master'=>5,'admin'=>4,'manager'=>3,'editor'=>2,'customer'=>1
    ];

    protected array $roles = UserDetail::ROLES;

    // Allowed roles for the current viewer (tabs + creation targets)
    protected function allowedRolesForViewer(): array
    {
        $user = auth()->user();
        if ($user->hasRole('master')) return array_keys(self::ROLE_LEVELS);

        $viewerLevel = $this->highestLevel($user);
        return array_values(array_filter(array_keys(self::ROLE_LEVELS), function ($role) use ($viewerLevel) {
            return (self::ROLE_LEVELS[$role] <= $viewerLevel);
        }));
    }

    protected function highestLevel($user): int
    {
        $levels = array_map(
            fn($r) => self::ROLE_LEVELS[$r->name] ?? 0,
            $user->roles->all()
        );
        return max($levels ?: [0]);
    }

    public function index(Request $request)
    {
        $allowed = $this->allowedRolesForViewer();
        $role = $request->string('role')->toString();

        // default to the highest allowed tab, or customer if empty
        if (!$role || !in_array($role, $allowed, true)) {
            $role = end($allowed); // ends at lowest, so prefer explicit:
            $role = in_array('customer', $allowed, true) ? 'customer' : $allowed[0];
            return redirect()->route('users.index', ['role' => $role]);
        }

        $this->authorize('viewAny', UserDetail::class);

        $users = UserDetail::with('user')
                           ->where('role', $role)
                           ->latest('updated_at')
                           ->get();

        return view('back.users.index', [
            'users'  => $users,
            'role'   => $role,
            'roles'  => $allowed, // limit tabs to allowed
        ]);
    }

    public function create(Request $request)
    {
        $allowed = $this->allowedRolesForViewer();
        $role = $request->string('role', 'customer')->toString();
        if (!in_array($role, $allowed, true)) {
            $role = $allowed[0];
        }

        $detail = new UserDetail(['role' => $role, 'status' => true]);
        $this->authorize('create', UserDetail::class);

        return view('back.users.create', [
            'detail' => $detail,
            'roles'  => $allowed, // restrict select
        ]);
    }
    
    public function store(Request $request)
    {
        $data = $this->validatePayload($request, true);
        
        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name'     => trim($data['fname'].' '.($data['lname'] ?? '')) ?: $data['email'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $detail = new UserDetail($data);
            $detail->user()->associate($user);

            $user->syncRoles([$data['role']]);

            // Avatar upload (optional)
            if ($request->hasFile('avatar_file')) {
                $path = $request->file('avatar_file')->store('avatars', 'public');
                $detail->avatar = 'storage/'.$path; // served via /storage symlink
            }
            
            $detail->save();
        });
        
        return redirect()->route('users.index', ['role' => $data['role']])
            ->with('success', __('back/users.flash.created'));
    }

    public function edit(UserDetail $user)
    {
        $this->authorize('view', $user);
        $roles = $this->allowedRolesForViewer();
        return view('back.users.edit', ['detail' => $user, 'roles' => $roles]);
    }

    public function update(Request $request, UserDetail $user)
    {
        $this->authorize('update', $user);
        $allowed = $this->allowedRolesForViewer();

        $data = $this->validatePayload($request, false, $user->user_id);

        if (!in_array($data['role'], $allowed, true)) {
            abort(403);
        }

        // Update base User
        $user->user->update([
            'name'     => trim($data['fname'].' '.($data['lname'] ?? '')) ?: $user->user->name,
            'email'    => $data['email'],
            'password' => !empty($data['password'])
                ? \Illuminate\Support\Facades\Hash::make($data['password'])
                : $user->user->password,
        ]);

        // Update detail fields (incl. status)
        $user->fill($data);

        // Avatar remove / upload
        if ($request->boolean('remove_avatar')) {
            $user->avatar = 'media/avatars/default_avatar.png';
        }
        if ($request->hasFile('avatar_file')) {
            $path = $request->file('avatar_file')->store('avatars', 'public');
            $user->avatar = 'storage/'.$path;
        }

        $user->save();

        // Keep roles in sync
        $user->user->syncRoles([$data['role']]);

        return redirect()->route('users.index', ['role' => $user->role])
                         ->with('success', __('back/users.flash.updated'));
    }


    // --------- Self profile (uses same form) ----------
    public function profile(Request $request)
    {
        $detail = $request->user()->detail;

        if (!$detail) {
            $detail = new UserDetail([
                'user_id' => $request->user()->id,
                'role'    => $request->user()->roles->first()->name ?? 'customer',
                'status'  => true,
            ]);
            $detail->save();
        }

        // Viewer can always edit self
        $roles = $this->allowedRolesForViewer(); // select will still show allowed options
        return view('back.users.edit', ['detail' => $detail, 'roles' => $roles]);
    }

    public function profileUpdate(Request $request)
    {
        $detail = $request->user()->detail;
        abort_unless($detail, 404);

        $this->authorize('update', $detail); // policy allows self

        $data = $this->validatePayload($request, false, $detail->user_id);

        // A non-master shouldn’t be able to upgrade their own role beyond allowed
        $allowed = $this->allowedRolesForViewer();
        if (!in_array($data['role'], $allowed, true)) {
            // lock role to current in case someone tampers
            $data['role'] = $detail->role;
        }

        // Reuse update logic:
        $detail->user->update([
            'name'  => trim($data['fname'].' '.($data['lname'] ?? '')) ?: $detail->user->name,
            'email' => $data['email'],
            'password' => !empty($data['password']) ? Hash::make($data['password']) : $detail->user->password,
        ]);
        $detail->fill($data);

        if ($request->boolean('remove_avatar')) {
            $detail->avatar = 'media/avatars/default_avatar.png';
        }
        if ($request->hasFile('avatar_file')) {
            $path = $request->file('avatar_file')->store('avatars', 'public');
            $detail->avatar = 'storage/'.$path;
        }
        $detail->save();

        $detail->user->syncRoles([$data['role']]);

        return redirect()->route('users.profile')->with('success', __('back/users.flash.updated'));
    }
    
    public function destroy(UserDetail $user)
    {
        $role = $user->role;
        $user->user->delete(); // cascades detail via FK
        return redirect()->route('users.index', ['role' => $role])
            ->with('success', __('back/users.flash.deleted'));
    }

    protected function validatePayload(Request $request, bool $creating, ?int $ignoreUserId = null): array
    {
        $validated = $request->validate([
            'role'    => ['required', Rule::in($this->roles)],
            'status'  => ['nullable', 'boolean'],

            'fname'   => ['required', 'string', 'max:255'],
            'lname'   => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'zip'     => ['nullable', 'string', 'max:20'],
            'city'    => ['nullable', 'string', 'max:120'],
            'state'   => ['nullable', 'string', 'max:120'],
            'phone'   => ['nullable', 'string', 'max:40'],
            'bio'     => ['nullable', 'string'],
            'social'  => ['nullable', 'string', 'max:255'],

            'email'    => ['required', 'email', 'max:255', Rule::unique('users','email')->ignore($ignoreUserId)],
            'password' => [$creating ? 'required' : 'nullable', 'min:8', 'confirmed'], // ← add confirmed
            // NOTE: no rule needed for password_confirmation; the 'confirmed' rule expects the field.

            'avatar_file'   => ['nullable','image','mimes:jpeg,jpg,png,webp','max:4096'],
            'remove_avatar' => ['nullable','boolean'],
        ]);

        $validated['status'] = $request->boolean('status', $creating ? true : false);
        return $validated;
    }



    protected function normalizeRole(string $role): string
    {
        return in_array($role, $this->roles, true) ? $role : 'customer';
    }
}
