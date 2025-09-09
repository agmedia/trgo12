<?php
// app/Http/Requests/Back/Catalog/StoreManufacturerRequest.php
namespace App\Http\Requests\Back\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManufacturerRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user()?->can('manage-catalog') ?? false;
    }


    protected function prepareForValidation(): void
    {
        if ( ! $this->has('status')) {
            $this->merge(['status' => 0]);
        }
        if ( ! $this->has('featured')) {
            $this->merge(['featured' => 0]);
        }
    }


    public function rules(): array
    {
        $rules = [
            'status'           => ['required', 'boolean'],
            'featured'         => ['required', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'website_url'      => ['nullable', 'url'],
            'support_email'    => ['nullable', 'email'],
            'phone'            => ['nullable', 'string', 'max:64'],
            'country_code'     => ['nullable', 'string', 'size:2'],
            'established_year' => ['nullable', 'integer', 'digits:4', 'min:1800', 'max:' . date('Y')],
            'logo_path'        => ['nullable', 'string', 'max:255'],

            'categories'   => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ];

        foreach (config('app.locales') as $code => $label) {
            $lang                            = is_string($code) ? $code : (string) $label;
            $rules["title.$lang"]            = ['required', 'string', 'max:255'];
            $rules["slug.$lang"]             = ['nullable', 'string', 'max:255',
                                                Rule::unique('manufacturer_translations', 'slug')->where('locale', $lang),
            ];
            $rules["description.$lang"]      = ['nullable', 'string'];
            $rules["meta_title.$lang"]       = ['nullable', 'string', 'max:255'];
            $rules["meta_description.$lang"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}
