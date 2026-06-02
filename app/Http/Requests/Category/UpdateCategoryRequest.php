<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('id');

        return [
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                "not_in:{$categoryId}" // Rule: A category cannot be its own parent
            ],
            'icon'      => ['nullable', 'string', 'max:255'],
            'image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
    public function bodyParameters(): array
    {
        return [
            'name'      => ['description' => 'The visible name of the category.', 'example' => 'Stadiums'],
            'parent_id' => ['description' => 'The ID of the parent category. Leave null for main categories.', 'example' => null],
            'icon'      => ['description' => 'Icon class identifier.', 'example' => 'fa-futbol'],
            'image'     => [
                'description' => 'Binary category logo/representation image file.',
                'example' => null // نغيره إلى null ليتخطى Scribe محاولة قراءته كمسار نصي فارغ
            ],
        ];
    }
}
