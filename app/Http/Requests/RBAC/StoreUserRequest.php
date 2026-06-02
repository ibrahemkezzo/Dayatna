<?php

namespace App\Http\Requests\RBAC;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'phone'         => 'required|string|max:20|unique:users,phone',
            'password'      => 'required|string|min:8',
            'role'          => 'required|string|exists:roles,name',
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
                'description' => 'The full name of the new user account.',
                'example' => 'Omar Mansour',
            ],
            'email' => [
                'description' => 'Unique email address constraint representation for the new account.',
                'example' => 'omar.manager@example.com',
            ],
            'phone' => [
                'description' => 'The unique authentication phone line for entry.',
                'example' => '+963932222222',
            ],
            'password' => [
                'description' => 'Secure raw password configuration (Minimum 8 characters).',
                'example' => 'AdminSecurePass2026',
            ],
            'role' => [
                'description' => 'The designated core role assigned by the administrator.',
                'example' => 'provider',
            ],
            'is_active' => [
                'description' => 'Initial active operational state status flag.',
                'example' => true,
            ],
        ];
    }
}
