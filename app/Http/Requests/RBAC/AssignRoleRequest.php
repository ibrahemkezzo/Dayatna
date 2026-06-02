<?php

declare(strict_types=1);

namespace App\Http\Requests\RBAC;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role'    => ['required', 'string', 'exists:roles,name'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'user_id' => [
                'description' => 'The ID of the target user.',
                'example'     => 2,
            ],
            'role' => [
                'description' => 'The name of the role to assign to the user.',
                'example'     => 'owner',
            ],
        ];
    }
}
