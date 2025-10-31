<?php
// app/Http/Controllers/Back/Catalog/Product/AttributeController.php
namespace App\Http\Controllers\Back\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Back\Catalog\Product\{
    ProductAttribute, ProductAttributeTranslation,
    ProductAttributeValue, ProductAttributeValueTranslation
};

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(config('settings.product_attributes_enabled'), 404);

        $q = ProductAttribute::query()
                             ->with(['translations','attributeValues.translations'])
                             ->orderBy('sort_order');

        if ($s = $request->string('q')->toString()) {
            $q->whereHas('translations', fn($t) => $t->where('title','like',"%{$s}%"));
        }

        $attributes = $q->paginate(20)->appends($request->query());
        return view('back.catalog.attribute.index', compact('attributes'));
    }

    public function create()
    {
        abort_unless(config('settings.product_attributes_enabled'), 404);
        $attribute = new ProductAttribute();
        $values = collect();
        return view('back.catalog.attribute.edit', compact('attribute','values'));
    }

    public function store(Request $request)
    {
        abort_unless(config('settings.product_attributes_enabled'), 404);

        $rules = [
            'status'       => ['sometimes','boolean'],
            'is_filterable'=> ['sometimes','boolean'],
            'is_visible'   => ['sometimes','boolean'],
            'sort_order'   => ['nullable','integer','min:0'],
            'values'       => ['nullable','array'],
            'values.*.status'     => ['sometimes','boolean'],
            'values.*.sort_order' => ['nullable','integer','min:0'],
        ];
        foreach (config('app.locales') as $code => $label) {
            $locale = is_string($code) ? $code : (string) $label;
            $rules["title.$locale"] = ['required','string','max:255'];
            $rules["slug.$locale"]  = ['nullable','string','max:255'];
            $rules["values.*.title.$locale"] = ['required_with:values','string','max:255'];
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($data, &$attribute) {
            $attribute = ProductAttribute::create([
                'status'        => (bool)($data['status'] ?? true),
                'is_filterable' => (bool)($data['is_filterable'] ?? true),
                'is_visible'    => (bool)($data['is_visible'] ?? true),
                'sort_order'    => (int)($data['sort_order'] ?? 0),
            ]);

            foreach (config('app.locales') as $code => $label) {
                $locale = is_string($code) ? $code : (string) $label;
                ProductAttributeTranslation::create([
                    'attribute_id' => $attribute->id,
                    'locale'       => $locale,
                    'title'        => $data['title'][$locale] ?? '',
                    'slug'         => ($data['slug'][$locale] ?? null) ?: str($data['title'][$locale] ?? '')->slug(),
                ]);
            }

            foreach ($data['values'] ?? [] as $v) {
                $val = ProductAttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'status'       => (bool)($v['status'] ?? true),
                    'sort_order'   => (int)($v['sort_order'] ?? 0),
                ]);
                foreach (config('app.locales') as $code => $label) {
                    $locale = is_string($code) ? $code : (string) $label;
                    ProductAttributeValueTranslation::create([
                        'value_id' => $val->id,
                        'locale'   => $locale,
                        'title'    => $v['title'][$locale] ?? '',
                    ]);
                }
            }
        });

        return redirect()->route('catalog.attributes.index')->with('success','Attribute created.');
    }

    public function edit(ProductAttribute $product_attribute)
    {
        abort_unless(config('settings.product_attributes_enabled'), 404);
        $attribute = $product_attribute->load(['translations','attributeValues.translations']);
        $values = $attribute->attributeValues;
        return view('back.catalog.attribute.edit', compact('attribute','values'));
    }

    public function update(Request $request, ProductAttribute $product_attribute)
    {
        abort_unless(config('settings.product_attributes_enabled'), 404);

        $rules = [
            'status'       => ['sometimes','boolean'],
            'is_filterable'=> ['sometimes','boolean'],
            'is_visible'   => ['sometimes','boolean'],
            'sort_order'   => ['nullable','integer','min:0'],
            'values'       => ['nullable','array'],
            'values.*.id'  => ['nullable','integer','exists:product_attribute_values,id'],
            'values.*.status'     => ['sometimes','boolean'],
            'values.*.sort_order' => ['nullable','integer','min:0'],
        ];
        foreach (config('app.locales') as $code => $label) {
            $locale = is_string($code) ? $code : (string) $label;
            $rules["title.$locale"] = ['required','string','max:255'];
            $rules["slug.$locale"]  = ['nullable','string','max:255'];
            $rules["values.*.title.$locale"] = ['required_with:values','string','max:255'];
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $product_attribute) {
            $product_attribute->update([
                'status'        => (bool)($data['status'] ?? $product_attribute->status),
                'is_filterable' => (bool)($data['is_filterable'] ?? $product_attribute->is_filterable),
                'is_visible'    => (bool)($data['is_visible'] ?? $product_attribute->is_visible),
                'sort_order'    => (int)($data['sort_order'] ?? $product_attribute->sort_order),
            ]);

            foreach (config('app.locales') as $code => $label) {
                $locale = is_string($code) ? $code : (string) $label;
                $product_attribute->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $data['title'][$locale] ?? '',
                        'slug'  => ($data['slug'][$locale] ?? null) ?: str($data['title'][$locale] ?? '')->slug(),
                    ]
                );
            }

            $keep = [];
            foreach ($data['values'] ?? [] as $v) {
                $val = isset($v['id'])
                    ? ProductAttributeValue::where('attribute_id',$product_attribute->id)->findOrFail($v['id'])
                    : ProductAttributeValue::create(['attribute_id'=>$product_attribute->id]);

                $val->update([
                    'status'     => (bool)($v['status'] ?? true),
                    'sort_order' => (int)($v['sort_order'] ?? 0),
                ]);

                foreach (config('app.locales') as $code => $label) {
                    $locale = is_string($code) ? $code : (string) $label;
                    $val->translations()->updateOrCreate(
                        ['locale'=>$locale],
                        ['title'=>$v['title'][$locale] ?? '']
                    );
                }
                $keep[] = $val->id;
            }

            $product_attribute->attributeValues()->whereNotIn('id',$keep)->delete();
        });

        return redirect()->route('catalog.attributes.edit', $product_attribute)->with('success','Attribute updated.');
    }
}
