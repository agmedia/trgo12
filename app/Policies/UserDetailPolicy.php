<?php

namespace App\Policies;

use App\Models\Back\User\UserDetail;
use App\Models\User;

class UserDetailPolicy
{
    // same mapping used in controller (keep in sync)
    private const ROLE_LEVELS = [
        'master' => 5, 'admin' => 4, 'manager' => 3, 'editor' => 2, 'customer' => 1,
    ];

    public function viewAny(User $user): bool
    {
        return true; // listing is allowed; filtering happens in controller
    }

    public function view(User $user, UserDetail $detail): bool
    {
        if ($user->id === $detail->user_id) return true; // self
        return $this->canReach($user, $detail->role);
    }

    public function create(User $user): bool
    {
        return true; // allowed to open form; options are restricted in form/controller
    }

    public function update(User $user, UserDetail $detail): bool
    {
        if ($user->id === $detail->user_id) return true; // self
        return $this->canReach($user, $detail->role);
    }

    public function delete(User $user, UserDetail $detail): bool
    {
        if ($user->id === $detail->user_id) return false; // don’t allow deleting self
        return $this->canReach($user, $detail->role);
    }

    private function canReach(User $viewer, string $targetRole): bool
    {
        $viewerLevel = $this->highestLevel($viewer);
        $targetLevel = self::ROLE_LEVELS[$targetRole] ?? 0;
        return $targetLevel <= $viewerLevel;
    }

    private function highestLevel(User $user): int
    {
        $levels = array_map(
            fn($r) => self::ROLE_LEVELS[$r->name] ?? 0,
            $user->roles->all()
        );
        return max($levels ?: [0]);
    }
}
