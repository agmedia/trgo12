<?php

namespace App\Http\Controllers\Back\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\Catalog\StoreProductRequest;
use App\Http\Requests\Back\Catalog\UpdateProductRequest;
use App\Models\Back\Catalog\Category;
use App\Models\Back\Catalog\Manufacturer;
use App\Models\Back\Catalog\Product\{Product, ProductOption, ProductTranslation};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        $query = Product::query()->with(['translations', 'categories']);

        if ($search = $request->string('q')->toString()) {
            $query->whereHas('translations', fn($t) => $t->where('title', 'like', '%' . $search . '%'));
        }

        if (($status = $request->get('status')) !== null && $status !== '') {
            $query->where('status', (bool) $status);
        }

        if ($manufacturerId = $request->integer('manufacturer')) {
            $query->where('manufacturer_id', $manufacturerId);
        }

        $products   = $query->latest('id')->paginate(20)->appends($request->query());
        $categories = $this->categoryList();
        $manufacturers = $this->manufacturerList();

        return view('back.catalog.product.index', compact('products', 'categories', 'manufacturers'));
    }


    public function create()
    {
        $product    = new Product();
        $categories = $this->categoryList();
        $manufacturers = $this->manufacturerList();
        $optionTree = $this->optionTreeForSelect();

        return view('back.catalog.product.edit', compact('product', 'categories', 'manufacturers', 'optionTree'));
    }


    public function store(StoreProductRequest $request)
    {
        DB::transaction(function () use ($request, &$product) {
            $payload = $request->safe()->except(['title', 'description', 'slug', 'categories', 'images', 'option_values']);
            $product = Product::create($payload);

            foreach (config('app.locales') as $code => $label) {
                ProductTranslation::create([
                    'product_id'  => $product->id,
                    'lang'        => $code,
                    'title'       => $request->input("title.$code"),
                    'slug'        => $request->input("slug.$code") ?: str($request->input("title.$code"))->slug(),
                    'description' => $request->input("description.$code"),
                ]);
            }

            if (config('settings.product_options_enabled')) {
                $items = collect($request->input('option_items', []))
                    ->keyBy('value_id') // key: option_value_id
                    ->map(function ($row) {
                        return [
                            'product_image_id' => $row['product_image_id'] ?? null,
                            'sku_full'         => $row['sku_full'] ?? null,
                            'sku_suffix'       => $row['sku_suffix'] ?? null,
                            'quantity'         => (int) ($row['quantity'] ?? 0),
                            'price_delta'      => (float) ($row['price_delta'] ?? 0),
                            'price_override'   => $row['price_override'] !== null && $row['price_override'] !== '' ? (float) $row['price_override'] : null,
                            'is_default'       => !empty($row['is_default']),
                            'extra'            => $row['extra'] ?? null,
                        ];
                    })->all();

                $product->optionValues()->sync($items); // sync with pivot data
            }


            $product->categories()->sync($request->input('categories'));
        });

        return redirect()->route('catalog.products.index')->with('success', 'Product created.');
    }


    public function edit(Product $product)
    {
        $product->load(['translations','categories','images', 'optionValues'] + (config('settings.product_options_enabled') ? ['optionValues.option'] : []));
        $categories = $this->categoryList();
        $manufacturers = $this->manufacturerList();
        $optionTree = $this->optionTreeForSelect();

        return view('back.catalog.product.edit', compact('product', 'categories', 'manufacturers', 'optionTree'));
    }


    public function update(UpdateProductRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            $payload = $request->safe()->except(['title', 'description', 'slug', 'categories', 'images']);
            $product->update($payload);

            foreach (config('app.locales') as $code => $label) {
                $product->translations()->updateOrCreate(
                    ['lang' => $code],
                    [
                        'title'       => $request->input("title.$code"),
                        'slug'        => $request->input("slug.$code") ?: str($request->input("title.$code"))->slug(),
                        'description' => $request->input("description.$code"),
                    ]
                );
            }

            if (config('settings.product_options_enabled')) {
                $items = collect($request->input('option_items', []))
                    ->keyBy('value_id') // key: option_value_id
                    ->map(function ($row) {
                        return [
                            'product_image_id' => $row['product_image_id'] ?? null,
                            'sku_full'         => $row['sku_full'] ?? null,
                            'sku_suffix'       => $row['sku_suffix'] ?? null,
                            'quantity'         => (int) ($row['quantity'] ?? 0),
                            'price_delta'      => (float) ($row['price_delta'] ?? 0),
                            'price_override'   => $row['price_override'] !== null && $row['price_override'] !== '' ? (float) $row['price_override'] : null,
                            'is_default'       => !empty($row['is_default']),
                            'extra'            => $row['extra'] ?? null,
                        ];
                    })->all();

                $product->optionValues()->sync($items); // sync with pivot data
            }

            $product->categories()->sync($request->input('categories'));
        });

        return redirect()->route('catalog.products.edit', $product)->with('success', 'Product updated.');
    }


    private function categoryList()
    {
        $locale   = app()->getLocale();
        $fallback = config('app.fallback_locale', $locale);

        // If your translation table uses "locale" instead of "lang", swap the column name below.
        // If it uses "name" instead of "title", swap the selected column too.
        return Category::query()
                       ->leftJoin('category_translations as t1', function ($j) use ($locale) {
                           $j->on('t1.category_id', '=', 'categories.id')
                             ->where('t1.locale', $locale);   // or ->where('t1.locale', $locale)
                       })
                       ->leftJoin('category_translations as t2', function ($j) use ($fallback) {
                           $j->on('t2.category_id', '=', 'categories.id')
                             ->where('t2.locale', $fallback); // or ->where('t2.locale', $fallback)
                       })
                       ->select('categories.id', DB::raw('COALESCE(t1.title, t2.title) as t')) // or COALESCE(t1.name, t2.name)
                       ->orderBy('t')
                       ->pluck('t', 'categories.id');
    }


    private function manufacturerList()
    {
        $locale   = app()->getLocale();
        $fallback = config('app.fallback_locale', $locale);

        return Manufacturer::query()
                           ->leftJoin('manufacturer_translations as t1', function ($j) use ($locale) {
                               $j->on('t1.manufacturer_id', '=', 'manufacturers.id')->where('t1.locale', $locale);
                           })
                           ->leftJoin('manufacturer_translations as t2', function ($j) use ($fallback) {
                               $j->on('t2.manufacturer_id', '=', 'manufacturers.id')->where('t2.locale', $fallback);
                           })
                           ->select('manufacturers.id', DB::raw('COALESCE(t1.title, t2.title) as t'))
                           ->orderBy('t')
                           ->pluck('t', 'manufacturers.id');
    }



    private function optionTreeForSelect(): array
    {
        if (!config('settings.product_options_enabled')) return [];

        $locale   = app()->getLocale();
        $fallback = config('app.fallback_locale', $locale);

        // Get options + values with translated titles
        return ProductOption::query()
                            ->where('status', true)
                            ->with(['optionValues' => function ($q) { $q->where('status', true)->orderBy('sort_order'); }])
                            ->orderBy('sort_order')
                            ->get()
                            ->map(function ($opt) use ($locale, $fallback) {
                                $optTitle = optional($opt->translation($locale))->title
                                            ?? optional($opt->translation($fallback))->title
                                               ?? "Option #{$opt->id}";
                                $values = $opt->optionValues->map(function ($val) use ($locale, $fallback, $optTitle) {
                                    $valTitle = optional($val->translation($locale))->title
                                                ?? optional($val->translation($fallback))->title
                                                   ?? "Value #{$val->id}";
                                    return ['id' => $val->id, 'label' => $valTitle];
                                });
                                return ['id' => $opt->id, 'title' => $optTitle, 'values' => $values];
                            })->all();
    }
}