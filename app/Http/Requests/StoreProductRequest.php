<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable', // Accept file or URL string
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'name.required' => 'Product name is required.',
            'slug.required' => 'Product slug is required.',
            'slug.unique' => 'A product with this slug already exists.',
            'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
            'description.required' => 'Product description is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price cannot be negative.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be an integer.',
            'stock.min' => 'Stock cannot be negative.',
        ];
    }

    /**
     * Custom validation for image_url (file upload or URL string).
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->hasFile('image_url')) {
                    $file = $this->file('image_url');
                    $ext = $file->getClientOriginalExtension();
                    $size = $file->getSize();

                    if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
                        $validator->errors()->add('image_url', 'Image must be a JPG, JPEG, or PNG file.');
                    }

                    if ($size > 2 * 1024 * 1024) { // 2MB in bytes
                        $validator->errors()->add('image_url', 'Image file size must not exceed 2MB.');
                    }
                } elseif ($this->filled('image_url')) {
                    // If image_url is provided as a string, validate it as a URL
                    $url = $this->input('image_url');
                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                        $validator->errors()->add('image_url', 'Image URL must be a valid URL format.');
                    }
                }
            }
        ];
    }
}
