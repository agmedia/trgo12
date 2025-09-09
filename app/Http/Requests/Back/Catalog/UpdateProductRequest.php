<?php
// app/Http/Requests/Back/Catalog/UpdateProductRequest.php

namespace App\Http\Requests\Back\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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

        $mid = $this->input('manufacturer_id');
        if ($mid === '0' || $mid === 0 || $mid === '' || $mid === null) {
            $this->merge(['manufacturer_id' => null]);
        }
    }


    public function rules(): array
    {
        // Route model binding may pass a model or an id
        $routeParam = $this->route('product');
        $productId  = is_object($routeParam) ? $routeParam->getKey() : (int) $routeParam;

        $rules = [
            'manufacturer_id' => ['nullable', 'integer', 'exists:manufacturers,id'],
            'sku'             => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($productId)],
            'price'           => ['required', 'numeric', 'min:0'],
            'status'          => ['required', 'boolean'],
            'categories'      => ['required', 'array', 'min:1'],
            'categories.*'    => ['integer', 'exists:categories,id'],
        ];

        // Per-locale fields
        foreach (config('app.locales') as $code => $label) {
            $rules["title.$code"]       = ['required', 'string', 'max:255'];
            $rules["description.$code"] = ['nullable', 'string'];
            $rules["slug.$code"]        = [
                'nullable',
                'string',
                'max:255',
                // slug must be unique within its locale among OTHER products
                Rule::unique('product_translations', 'slug')
                    ->where('locale', is_string($code) ? $code : (string) $label)
                    ->ignore($productId, 'product_id'),
            ];
        }

        return $rules;
    }
}
