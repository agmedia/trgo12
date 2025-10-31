<?php

// app/Http/Requests/Back/Catalog/StoreProductRequest.php

namespace App\Http\Requests\Back\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user()?->can('manage-catalog') ?? false;
    }


    protected function prepareForValidation(): void
    {
        // Ensure checkbox status is always present (unchecked -> 0)
        if ( ! $this->has('status')) {
            $this->merge(['status' => 0]);
        }

        // normalize manufacturer_id
        $mid = $this->input('manufacturer_id');
        if ($mid === '0' || $mid === 0 || $mid === '' || $mid === null) {
            $this->merge(['manufacturer_id' => null]);
        }
    }


    public function rules(): array
    {
        $rules = [
            'manufacturer_id' => ['nullable', 'integer', 'exists:manufacturers,id'],
            'sku'             => ['required', 'string', 'max:64', Rule::unique('products', 'sku')],
            'price'           => ['required', 'numeric', 'min:0'],
            'status'          => ['required', 'boolean'],
            'categories'      => ['required', 'array', 'min:1'],
            'categories.*'    => ['integer', 'exists:categories,id'],
        ];

        if (config('settings.product_options_enabled')) {
            $rules['option_values']   = ['nullable', 'array'];
            $rules['option_values.*'] = ['integer', 'exists:product_option_values,id'];
        }

        // Per-locale fields (uses config('app.locales'))
        foreach (config('app.locales') as $code => $label) {
            $lang = is_string($code) ? $code : (string) $label;

            $rules["title.$lang"]       = ['required', 'string', 'max:255'];
            $rules["description.$lang"] = ['nullable', 'string'];
            $rules["slug.$lang"]        = [
                'nullable',
                'string',
                'max:255',
                // slug must be unique within its locale across all products
                Rule::unique('product_translations', 'slug')->where('locale', $lang),
            ];
        }

        // Only when product options are enabled
        if (config('settings.product_options_enabled')) {
            // Expect structure:
            // option_items: [
            //   { value_id, product_image_id?, sku_full?, sku_suffix?, quantity, price_delta?, price_override?, is_default? },
            //   ...
            // ]
            $rules['option_items']                    = ['nullable', 'array'];
            $rules['option_items.*.value_id']         = ['required', 'integer', 'exists:product_option_values,id'];
            $rules['option_items.*.product_image_id'] = ['nullable', 'integer', 'exists:product_images,id'];
            $rules['option_items.*.sku_full']         = ['nullable', 'string', 'max:128', 'distinct', Rule::unique('product_option_value_product', 'sku_full')];
            $rules['option_items.*.sku_suffix']       = ['nullable', 'string', 'max:32'];
            $rules['option_items.*.quantity']         = ['required', 'integer', 'min:0'];
            $rules['option_items.*.price_delta']      = ['nullable', 'numeric'];
            $rules['option_items.*.price_override']   = ['nullable', 'numeric'];
            $rules['option_items.*.is_default']       = ['sometimes', 'boolean'];
        }

        if (config('settings.product_attributes_enabled')) {
            // očekujemo: attribute_items[] = { value_id, sort_order?, share_percent?, amount?, unit?, note?, is_primary? }
            $rules['attribute_items']                 = ['nullable', 'array'];
            $rules['attribute_items.*.value_id']      = ['required', 'integer', 'distinct', 'exists:product_attribute_values,id'];
            $rules['attribute_items.*.sort_order']    = ['nullable', 'integer', 'min:0'];
            $rules['attribute_items.*.share_percent'] = ['nullable', 'numeric', 'min:0', 'max:100'];
            $rules['attribute_items.*.amount']        = ['nullable', 'numeric', 'min:0'];
            $rules['attribute_items.*.unit']          = ['nullable', 'string', 'max:16'];
            $rules['attribute_items.*.note']          = ['nullable', 'string', 'max:255'];
            $rules['attribute_items.*.is_primary']    = ['sometimes', 'boolean'];
        }

        return $rules;
    }


    public function withValidator($validator)
    {
        if ( ! config('settings.product_attributes_enabled')) {
            return;
        }

        $validator->after(function ($v) {
            $items = collect($this->input('attribute_items', []));

            if ($items->isEmpty()) {
                return;
            }

            // 1) Svaki red mora imati bar share_percent ili amount
            $items->each(function ($row, $i) use ($v) {
                if (($row['share_percent'] ?? null) === null && ($row['amount'] ?? null) === null) {
                    $v->errors()->add("attribute_items.$i.share_percent", 'Provide share_percent or amount.');
                    $v->errors()->add("attribute_items.$i.amount", 'Provide share_percent or amount.');
                }
            });

            // 2) Ako je atribut kompozitan → zbroj postotaka = 100
            $valueIds = $items->pluck('value_id')->filter()->all();
            if ($valueIds) {
                $vals = ProductAttributeValue::with('attribute')->whereIn('id', $valueIds)->get()->keyBy('id');
                // grupiraj po attribute_id
                $byAttr = $items->groupBy(function ($row) use ($vals) {
                    $val = $vals[$row['value_id']] ?? null;

                    return $val?->attribute_id;
                });

                foreach ($byAttr as $attrId => $rows) {
                    $attr = optional($vals[$rows->first()['value_id']] ?? null)->attribute;
                    if ($attr && $attr->is_composite) {
                        $sum = $rows->sum(function ($r) {
                            return (float) ($r['share_percent'] ?? 0);
                        });
                        // dopuštamo minimalno odstupanje 0.01
                        if (abs($sum - 100) > 0.01) {
                            $v->errors()->add('attribute_items', "Sum of percentages for '{$attr->translation()?->title}' must be 100 (currently {$sum}).");
                        }
                    }
                }
            }
        });
    }
}
