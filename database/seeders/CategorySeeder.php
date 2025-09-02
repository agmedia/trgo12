<?php

namespace Database\Seeders;

use App\Models\Back\Catalog\Category;
use App\Models\Back\Catalog\CategoryTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds demo category trees for groups: products/blog/pages/footer
 * - Only runs if categories table is empty (safe to re-run).
 * - Writes concise console output about what it's doing.
 */
class CategorySeeder extends Seeder
{
    /** @var array<string, array> */
    private array $data;
    
    public function run(): void
    {
        // Safety: don't duplicate data
        if (Category::count() > 0) {
            $this->command?->warn('CategorySeeder: categories table not empty — skipping.');
            return;
        }
        
        // Define locales (hr/en by default). You can set config('app.locales') to control this.
        $locales = config('app.locales', ['hr', 'en']);
        
        // --- Demo tree data: edit freely for your project needs ---
        $this->data = [
            'products' => [
                [
                    'flags' => ['is_active' => true, 'is_navbar' => true],
                    'translations' => [
                        'hr' => ['title' => 'Elektronika'],
                        'en' => ['title' => 'Electronics'],
                    ],
                    'children' => [
                        [
                            'translations' => [
                                'hr' => ['title' => 'Laptopi'],
                                'en' => ['title' => 'Laptops'],
                            ],
                        ],
                        [
                            'translations' => [
                                'hr' => ['title' => 'Mobiteli'],
                                'en' => ['title' => 'Phones'],
                            ],
                            'children' => [
                                [
                                    'translations' => [
                                        'hr' => ['title' => 'Android'],
                                        'en' => ['title' => 'Android'],
                                    ],
                                ],
                                [
                                    'translations' => [
                                        'hr' => ['title' => 'iOS'],
                                        'en' => ['title' => 'iOS'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'flags' => ['is_active' => true, 'is_navbar' => true],
                    'translations' => [
                        'hr' => ['title' => 'Kućanstvo'],
                        'en' => ['title' => 'Home'],
                    ],
                    'children' => [
                        [
                            'translations' => [
                                'hr' => ['title' => 'Mali kućanski'],
                                'en' => ['title' => 'Appliances'],
                            ],
                        ],
                    ],
                ],
            ],
            'blog' => [
                [
                    'flags' => ['is_active' => true, 'is_navbar' => true],
                    'translations' => [
                        'hr' => ['title' => 'Novosti'],
                        'en' => ['title' => 'News'],
                    ],
                ],
                [
                    'flags' => ['is_active' => true],
                    'translations' => [
                        'hr' => ['title' => 'Vodiči'],
                        'en' => ['title' => 'Guides'],
                    ],
                ],
            ],
            'pages' => [
                [
                    'flags' => ['is_active' => true, 'is_navbar' => true],
                    'translations' => [
                        'hr' => ['title' => 'Informacije'],
                        'en' => ['title' => 'Info'],
                    ],
                    'children' => [
                        [
                            'translations' => [
                                'hr' => ['title' => 'Dostava'],
                                'en' => ['title' => 'Shipping'],
                            ],
                        ],
                        [
                            'translations' => [
                                'hr' => ['title' => 'Povrat'],
                                'en' => ['title' => 'Returns'],
                            ],
                        ],
                    ],
                ],
            ],
            'footer' => [
                [
                    'flags' => ['is_active' => true, 'is_footer' => true],
                    'translations' => [
                        'hr' => ['title' => 'O nama', 'link_url' => '/o-nama'],
                        'en' => ['title' => 'About us', 'link_url' => '/about'],
                    ],
                ],
                [
                    'flags' => ['is_active' => true, 'is_footer' => true],
                    'translations' => [
                        'hr' => ['title' => 'Kontakt', 'link_url' => '/kontakt'],
                        'en' => ['title' => 'Contact', 'link_url' => '/contact'],
                    ],
                ],
            ],
        ];
        
        // --- Console output like in top-tim style ---
        $this->command?->info('> Seeding Category trees...');
        $totalCreated = 0;
        
        DB::transaction(function () use (&$totalCreated, $locales) {
            foreach ($this->data as $group => $nodes) {
                $countPlanned = $this->countNodes($nodes);
                $this->command?->line("  • Group: <info>{$group}</info> (planned: {$countPlanned} nodes)");
                
                $createdForGroup = 0;
                
                foreach ($nodes as $i => $node) {
                    $createdForGroup += $this->createNode($group, $node, null, $locales);
                }
                
                $totalCreated += $createdForGroup;
                $this->command?->line("    └─ created: <comment>{$createdForGroup}</comment> node(s)");
            }
        });
        
        $this->command?->info("> Category seeding done. Total created: {$totalCreated} node(s).");
    }
    
    /**
     * Recursively create a node (and its children) under an optional parent.
     * Returns the number of nodes created (this node + descendants).
     */
    private function createNode(string $group, array $node, ?Category $parent, array $locales): int
    {
        $count = 0;
        
        // 1) Create category (root or child) with flags & position.
        $category = new Category();
        $category->fill(array_merge([
            'group'     => $group,
            'is_active' => true,
            'is_navbar' => false,
            'is_footer' => false,
            'position'  => 0,
        ], $node['flags'] ?? []));
        
        if ($parent) {
            $category->appendToNode($parent)->save();
        } else {
            $category->save();
        }
        $count++;
        
        // 2) Create per-locale translations (slug auto-derived if not provided).
        foreach ($locales as $locale) {
            $t = $node['translations'][$locale] ?? reset($node['translations']);
            
            CategoryTranslation::create([
                'category_id'     => $category->id,
                'locale'          => $locale,
                'title'           => $t['title'] ?? 'Untitled',
                'slug'            => !empty($t['slug'])
                    ? $t['slug']
                    : (isset($t['title']) ? Str::slug($t['title']) : null),
                'link_url'        => $t['link_url'] ?? null,     // used mainly for footer custom links
                'description'     => $t['description'] ?? null,
                'seo_title'       => $t['seo_title'] ?? ($t['title'] ?? null),
                'seo_description' => $t['seo_description'] ?? null,
                'seo_keywords'    => $t['seo_keywords'] ?? null,
                'seo_json'        => $t['seo_json'] ?? null,
            ]);
        }
        
        // 3) Recurse for children (if any).
        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $count += $this->createNode($group, $child, $category, $locales);
            }
        }
        
        return $count;
    }
    
    /**
     * Count how many nodes (this + descendants) are defined in the data array.
     */
    private function countNodes(array $nodes): int
    {
        $n = 0;
        foreach ($nodes as $node) {
            $n++;
            if (!empty($node['children'])) {
                $n += $this->countNodes($node['children']);
            }
        }
        return $n;
    }
}
