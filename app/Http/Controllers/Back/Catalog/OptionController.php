<?php
// app/Http/Controllers/Back/Catalog/OptionController.php

namespace App\Http\Controllers\Back\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Back\Catalog\Product\ProductOption;
use App\Models\Back\Catalog\Product\ProductOptionTranslation;
use App\Models\Back\Catalog\Product\ProductOptionValue;
use App\Models\Back\Catalog\Product\ProductOptionValueTranslation;

class OptionController extends Controller
{

    public function index(Request $request)
    {
        abort_unless(config('settings.product_options_enabled'), 404);

        $q = ProductOption::query()
                          ->with(['translations', 'values.translations'])
                          ->orderBy('sort_order');

        if ($search = $request->string('q')->toString()) {
            $q->whereHas('translations', fn($t) => $t->where('title', 'like', "%{$search}%"));
        }

        $options = $q->paginate(20)->appends($request->query());

        return view('back.catalog.option.index', compact('options'));
    }


    public function create()
    {
        abort_unless(config('settings.product_options_enabled'), 404);
        $option = new ProductOption();
        $values = collect();

        return view('back.catalog.option.edit', compact('option', 'values'));
    }


    public function store(Request $request)
    {
        abort_unless(config('settings.product_options_enabled'), 404);

        $rules = [
            'status'              => ['sometimes', 'boolean'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'values'              => ['nullable', 'array'],
            'values.*.status'     => ['sometimes', 'boolean'],
            'values.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
        foreach (config('app.locales') as $code => $label) {
            $locale                 = is_string($code) ? $code : (string) $label;
            $rules["title.$locale"] = ['required', 'string', 'max:255'];
            $rules["slug.$locale"]  = ['nullable', 'string', 'max:255'];
            // Values titles per locale:
            $rules["values.*.title.$locale"] = ['required_with:values', 'string', 'max:255'];
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($data, &$option) {
            $option = ProductOption::create([
                'status'     => (bool) ($data['status'] ?? true),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            foreach (config('app.locales') as $code => $label) {
                $locale = is_string($code) ? $code : (string) $label;
                ProductOptionTranslation::create([
                    'option_id' => $option->id,
                    'locale'    => $locale,
                    'title'     => $data['title'][$locale] ?? '',
                    'slug'      => ($data['slug'][$locale] ?? null) ?: str($data['title'][$locale] ?? '')->slug(),
                ]);
            }

            foreach ($data['values'] ?? [] as $v) {
                $val = ProductOptionValue::create([
                    'option_id'  => $option->id,
                    'status'     => (bool) ($v['status'] ?? true),
                    'sort_order' => (int) ($v['sort_order'] ?? 0),
                ]);
                foreach (config('app.locales') as $code => $label) {
                    $locale = is_string($code) ? $code : (string) $label;
                    ProductOptionValueTranslation::create([
                        'value_id' => $val->id,
                        'locale'   => $locale,
                        'title'    => $v['title'][$locale] ?? '',
                    ]);
                }
            }
        });

        return redirect()->route('catalog.options.index')->with('success', 'Option created.');
    }


    public function edit(ProductOption $product_option)
    {
        abort_unless(config('settings.product_options_enabled'), 404);
        $option = $product_option->load(['translations', 'optionValues.translations']);
        $values = $option->optionValues;

        return view('back.catalog.option.edit', compact('option', 'values'));
    }


    public function update(Request $request, ProductOption $product_option)
    {
        abort_unless(config('settings.product_options_enabled'), 404);

        $rules = [
            'status'              => ['sometimes', 'boolean'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'values'              => ['nullable', 'array'],
            'values.*.id'         => ['nullable', 'integer', 'exists:product_option_values,id'],
            'values.*.status'     => ['sometimes', 'boolean'],
            'values.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        foreach (config('app.locales') as $code => $label) {
            $locale                          = is_string($code) ? $code : (string) $label;
            $rules["title.$locale"]          = ['required', 'string', 'max:255'];
            $rules["slug.$locale"]           = ['nullable', 'string', 'max:255'];
            $rules["values.*.title.$locale"] = ['required_with:values', 'string', 'max:255'];
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $product_option) {
            $product_option->update([
                'status'     => (bool) ($data['status'] ?? $product_option->status),
                'sort_order' => (int) ($data['sort_order'] ?? $product_option->sort_order),
            ]);

            foreach (config('app.locales') as $code => $label) {
                $locale = is_string($code) ? $code : (string) $label;
                $product_option->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $data['title'][$locale] ?? '',
                        'slug'  => ($data['slug'][$locale] ?? null) ?: str($data['title'][$locale] ?? '')->slug(),
                    ]
                );
            }

            // Upsert values
            $keepIds = [];
            foreach ($data['values'] ?? [] as $v) {
                $val = isset($v['id'])
                    ? ProductOptionValue::where('option_id', $product_option->id)->findOrFail($v['id'])
                    : ProductOptionValue::create(['option_id' => $product_option->id]);

                $val->update([
                    'status'     => (bool) ($v['status'] ?? true),
                    'sort_order' => (int) ($v['sort_order'] ?? 0),
                ]);

                foreach (config('app.locales') as $code => $label) {
                    $locale = is_string($code) ? $code : (string) $label;
                    $val->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['title' => $v['title'][$locale] ?? '']
                    );
                }

                $keepIds[] = $val->id;
            }

            // Remove deleted values
            $product_option->values()->whereNotIn('id', $keepIds)->delete();
        });

        return redirect()->route('catalog.options.edit', $product_option)->with('success', 'Option updated.');
    }
}
