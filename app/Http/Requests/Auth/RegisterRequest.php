<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'], // Added Email validation
            'phone'    => ['required', 'string', 'unique:users,phone', 'regex:/^09\d{8}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role'     => ['nullable', 'string', 'in:customer,owner,admin'], // Optional role field with allowed values
            'password_confirmation' => ['required', 'string'], // Explicitly added to align with Scribe and Laravel validation
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The full name of the user.',
                'example'     => 'John Doe'
            ],
            'email' => [
                'description' => 'The unique email address.',
                'example'     => 'johndoe@example.com'
            ],
            'phone' => [
                'description' => 'The unique phone number starting with 09.',
                'example'     => '0912345678'
            ],
            'password' => [
                'description' => 'The user password (min 8 characters, letters, and numbers).',
                'example'     => 'Password123' // Matching the confirmation example exactly
            ],
            'password_confirmation' => [
                'description' => 'The password confirmation field must match the password field.',
                'example'     => 'Password123' // Matching the password example exactly
            ],
            'role' => [
                'description' => 'The account type (user or provider).',
                'example'     => 'customer'
            ],
        ];
    }
}
