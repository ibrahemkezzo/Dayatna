<?php

declare(strict_types=1);

namespace App\Http\Requests\RBAC;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255', 'unique:permissions,name']];
    }

    public function bodyParameters(): array
    {
        return ['name' => ['description' => 'The unique permission name (action_entity).', 'example' => 'publish_blogs']];
    }
}
