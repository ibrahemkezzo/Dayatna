<?php

declare(strict_types=1);

namespace App\Http\Requests\RBAC;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SyncPermissionsRequest extends FormRequest
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
        return [
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['required', 'string', 'exists:permissions,name'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'permissions' => [
                'description' => 'Array of permission names to assign to the role. Missing permissions will be detached.',
                'example'     => ['view_facilities', 'edit_facilities'],
            ],
        ];
    }
}
