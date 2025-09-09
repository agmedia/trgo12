<?php
// app/Http/Controllers/Back/Catalog/ManufacturerController.php
namespace App\Http\Controllers\Back\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\Catalog\StoreManufacturerRequest;
use App\Http\Requests\Back\Catalog\UpdateManufacturerRequest;
use App\Models\Back\Catalog\{Manufacturer, ManufacturerTranslation, Category, Product\Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\DB as FacadesDB;

class ManufacturerController extends Controller
{

    public function index(Request $request)
    {
        $query = Manufacturer::query()->with(['translations'])
                             ->withCount('products');

        if ($search = $request->string('q')->toString()) {
            $query->whereHas('translations', fn($t) => $t->where('title', 'like', "%{$search}%"));
        }

        if (($status = $request->get('status')) !== null && $status !== '') {
            $query->where('status', (bool) $status);
        }

        $manufacturers = $query->latest('id')->paginate(20)->appends($request->query());
        $categories    = $this->categoryList();

        return view('back.catalog.manufacturer.index', compact('manufacturers', 'categories'));
    }


    public function create()
    {
        $manufacturer = new Manufacturer();
        $categories   = $this->categoryList();

        return view('back.catalog.manufacturer.edit', compact('manufacturer', 'categories'));
    }


    public function store(StoreManufacturerRequest $request)
    {
        DB::transaction(function () use ($request, &$manufacturer) {
            $payload      = $request->safe()->except(['title', 'slug', 'description', 'meta_title', 'meta_description', 'categories']);
            $manufacturer = Manufacturer::create($payload);

            foreach (config('app.locales') as $code => $label) {
                ManufacturerTranslation::create([
                    'manufacturer_id'  => $manufacturer->id,
                    'locale'           => is_string($code) ? $code : (string) $label,
                    'title'            => $request->input("title.$code"),
                    'slug'             => $request->input("slug.$code") ?: str($request->input("title.$code"))->slug(),
                    'description'      => $request->input("description.$code"),
                    'meta_title'       => $request->input("meta_title.$code"),
                    'meta_description' => $request->input("meta_description.$code"),
                ]);
            }

            if ($cats = $request->input('categories')) {
                $manufacturer->categories()->sync($cats);
            }
        });

        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer created.');
    }


    public function edit(Manufacturer $manufacturer)
    {
        $manufacturer->load(['translations', 'categories', 'products']);
        // Paginated list of assigned products (with translations for title)
        $products = Product::query()
                           ->with('translations')
                           ->where('manufacturer_id', $manufacturer->id)
                           ->latest('id')
                           ->paginate(15)
                           ->appends(request()->query());

        return view('back.catalog.manufacturer.edit', compact('manufacturer', 'products'));
    }


    public function update(UpdateManufacturerRequest $request, Manufacturer $manufacturer)
    {
        DB::transaction(function () use ($request, $manufacturer) {
            $payload = $request->safe()->except(['title', 'slug', 'description', 'meta_title', 'meta_description', 'categories']);
            $manufacturer->update($payload);

            foreach (config('app.locales') as $code => $label) {
                $lang = is_string($code) ? $code : (string) $label;
                $manufacturer->translations()->updateOrCreate(
                    ['locale' => $lang],
                    [
                        'title'            => $request->input("title.$code"),
                        'slug'             => $request->input("slug.$code") ?: str($request->input("title.$code"))->slug(),
                        'description'      => $request->input("description.$code"),
                        'meta_title'       => $request->input("meta_title.$code"),
                        'meta_description' => $request->input("meta_description.$code"),
                    ]
                );
            }

            $manufacturer->categories()->sync($request->input('categories', []));
        });

        return redirect()->route('catalog.manufacturers.edit', $manufacturer)->with('success', 'Manufacturer updated.');
    }


    // --- helpers ---
    private function categoryList()
    {
        $locale   = app()->getLocale();
        $fallback = config('app.fallback_locale', $locale);

        return Category::query()
                       ->leftJoin('category_translations as t1', function ($j) use ($locale) {
                           $j->on('t1.category_id', '=', 'categories.id')->where('t1.locale', $locale);
                       })
                       ->leftJoin('category_translations as t2', function ($j) use ($fallback) {
                           $j->on('t2.category_id', '=', 'categories.id')->where('t2.locale', $fallback);
                       })
                       ->select('categories.id', \DB::raw('COALESCE(t1.title, t2.title) as t'))
                       ->orderBy('t')
                       ->pluck('t', 'categories.id');
    }
}
