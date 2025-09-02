<?php

namespace App\Http\Controllers\Back\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Back\Catalog\Category;
use App\Models\Back\Catalog\CategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // public function __construct() { $this->middleware(['auth', 'role:admin']); }
    
    private array $groups = ['products', 'blog', 'pages', 'footer'];
    
    public function index(Request $request)
    {
        $group = $request->get('group', 'products');
        abort_unless(in_array($group, $this->groups, true), 404);
        
        $categories = Category::with('translations')
            ->forGroup($group)
            ->get()
            ->toTree();
        
        $locales = config('app.locales', [config('app.locale', 'hr')]);
        
        return view('back.catalog.categories.index', compact('categories', 'group', 'locales'));
    }
    
    public function create(Request $request)
    {
        $group   = $request->get('group', 'products');
        $locales = config('app.locales', [config('app.locale', 'hr')]);
        $parents = Category::forGroup($group)->get();
        
        return view('back.catalog.categories.create', compact('group', 'locales', 'parents'));
    }
    
    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        
        DB::transaction(function () use ($validated, $request) {
            $category = new Category();
            $category->fill([
                'group'     => $validated['group'],
                'is_active' => $validated['is_active'] ?? false,
                'is_navbar' => $validated['is_navbar'] ?? false,
                'is_footer' => $validated['is_footer'] ?? false,
                'position'  => $validated['position'] ?? 0,
            ]);
            
            if (!empty($validated['parent_id'])) {
                $parent = Category::findOrFail($validated['parent_id']);
                $category->appendToNode($parent)->save();
            } else {
                $category->save();
            }
            
            foreach ($validated['translations'] as $locale => $t) {
                CategoryTranslation::create([
                    'category_id'     => $category->id,
                    'locale'          => $locale,
                    'title'           => $t['title'] ?? null,
                    'slug'            => $t['slug'] ?? ($t['title'] ? Str::slug($t['title']) : null),
                    'link_url'        => $t['link_url'] ?? null,
                    'description'     => $t['description'] ?? null,
                    'seo_title'       => $t['seo_title'] ?? null,
                    'seo_description' => $t['seo_description'] ?? null,
                    'seo_keywords'    => $t['seo_keywords'] ?? null,
                    'seo_json'        => $t['seo_json'] ?? null,
                ]);
            }
            
            foreach (['image', 'icon', 'banner'] as $field) {
                if ($request->hasFile($field)) {
                    $category->addMediaFromRequest($field)->toMediaCollection($field);
                }
            }
        });
        
        return redirect()
            ->route('admin.catalog.categories.index', ['group' => $validated['group']])
            ->with('success', 'Category created.');
    }
    
    public function edit(Category $category)
    {
        $group   = $category->group;
        $locales = config('app.locales', [config('app.locale', 'hr')]);
        $parents = Category::forGroup($group)->where('id', '!=', $category->id)->get();
        
        $category->load('translations', 'media');
        
        return view('back.catalog.categories.edit', compact('category', 'group', 'locales', 'parents'));
    }
    
    public function update(Request $request, Category $category)
    {
        $validated = $this->validatePayload($request, updating: true);
        
        DB::transaction(function () use ($validated, $request, $category) {
            $category->fill([
                'group'     => $validated['group'],
                'is_active' => $validated['is_active'] ?? false,
                'is_navbar' => $validated['is_navbar'] ?? false,
                'is_footer' => $validated['is_footer'] ?? false,
                'position'  => $validated['position'] ?? 0,
            ]);
            
            if (array_key_exists('parent_id', $validated)) {
                if ($validated['parent_id']) {
                    $parent = Category::findOrFail($validated['parent_id']);
                    if (!$category->isDescendantOf($parent)) {
                        $category->appendToNode($parent)->save();
                    } else {
                        $category->save();
                    }
                } else {
                    $category->saveAsRoot();
                }
            } else {
                $category->save();
            }
            
            foreach ($validated['translations'] as $locale => $t) {
                $category->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title'           => $t['title'] ?? null,
                        'slug'            => $t['slug'] ?? ($t['title'] ? Str::slug($t['title']) : null),
                        'link_url'        => $t['link_url'] ?? null,
                        'description'     => $t['description'] ?? null,
                        'seo_title'       => $t['seo_title'] ?? null,
                        'seo_description' => $t['seo_description'] ?? null,
                        'seo_keywords'    => $t['seo_keywords'] ?? null,
                        'seo_json'        => $t['seo_json'] ?? null,
                    ]
                );
            }
            
            foreach (['image', 'icon', 'banner'] as $field) {
                if ($request->hasFile($field)) {
                    $category->clearMediaCollection($field);
                    $category->addMediaFromRequest($field)->toMediaCollection($field);
                }
            }
        });
        
        return redirect()
            ->route('admin.catalog.categories.index', ['group' => $validated['group']])
            ->with('success', 'Category updated.');
    }
    
    public function destroy(Category $category)
    {
        $group = $category->group;
        $category->delete();
        
        return redirect()
            ->route('admin.catalog.categories.index', ['group' => $group])
            ->with('success', 'Category deleted.');
    }
    
    private function validatePayload(Request $request, bool $updating = false): array
    {
        $locales = config('app.locales', [config('app.locale', 'hr')]);
        
        $rules = [
            'group'      => 'required|string|in:products,blog,pages,footer',
            'parent_id'  => 'nullable|integer|exists:categories,id',
            'is_active'  => 'sometimes|boolean',
            'is_navbar'  => 'sometimes|boolean',
            'is_footer'  => 'sometimes|boolean',
            'position'   => 'sometimes|integer|min:0',
            'translations' => 'required|array',
        ];
        
        foreach ($locales as $locale) {
            $rules["translations.$locale.title"]           = $updating ? 'nullable|string|max:255' : 'required|string|max:255';
            $rules["translations.$locale.slug"]            = 'nullable|string|max:255';
            $rules["translations.$locale.link_url"]        = 'nullable|url|max:2048';
            $rules["translations.$locale.description"]     = 'nullable|string';
            $rules["translations.$locale.seo_title"]       = 'nullable|string|max:255';
            $rules["translations.$locale.seo_description"] = 'nullable|string|max:255';
            $rules["translations.$locale.seo_keywords"]    = 'nullable|string|max:255';
            $rules["translations.$locale.seo_json"]        = 'nullable|array';
        }
        
        return $request->validate($rules);
    }
}
