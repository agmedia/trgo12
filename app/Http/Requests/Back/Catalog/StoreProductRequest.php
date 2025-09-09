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

        return $rules;
    }
}
