<?php

namespace App\Http\Requests\RBAC;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id');

        return [
            'name'          => 'sometimes|required|string|max:255',
            'email'         => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'phone'         => ['sometimes', 'required', 'string', 'max:20', Rule::unique('users')->ignore($userId)],
            'password'      => 'nullable|string|min:8',
            'role'          => 'sometimes|required|string|exists:roles,name',
            'is_active'     => 'sometimes|required|boolean',
            'is_searchable' => 'sometimes|required|boolean',

            // Optional direct permissions array
            'permissions'   => 'nullable|array',
            'permissions.*' => 'required|string|exists:permissions,name',
        ];
    }

    /**
     * Define body parameters for Scribe documentation.
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The updated full name of the user.',
                'example' => 'Ahmaed Al-Khatib',
            ],
            'email' => [
                'description' => 'Unique email address constraint representation.',
                'example' => 'ahmed.admin@example.com',
            ],
            'phone' => [
                'description' => 'The active mobile authentication line.',
                'example' => '+963931111111',
            ],
            'password' => [
                'description' => 'New secure password configuration string (Minimum 8 chars).',
                'example' => 'NewSecurePass123',
            ],
            'role' => [
                'description' => 'The core designative application role string mapping.',
                'example' => 'admin',
            ],
            'is_active' => [
                'description' => 'Toggle flag to enable or completely suspend account access.',
                'example' => true,
            ],
            'permissions' => [
                'description' => 'An array of descriptive permission names to assign directly to the user instance.',
                'example' => ['create entities', 'edit entities']
            ]
        ];
    }
}
