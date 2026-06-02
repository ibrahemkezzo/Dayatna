<?php

declare(strict_types=1);

namespace App\Services\RBAC;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Handle user registration and issue an API token.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => $data['password'],
            'role'     => $data['role'] ?? 'user',
        ]);

        $token = $user->createToken('dayatna_auth_token')->plainTextToken;

        return [$user, $token];
    }

    /**
     * Authenticate user credentials and issue an API token.
     */
    public function login(array $credentials): array
    {
        $user = User::where('phone', $credentials['phone'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'phone' => ['This account has been deactivated by the administrator.'],
            ]);
        }

        $token = $user->createToken('dayatna_auth_token')->plainTextToken;

        return [$user, $token];
    }

    /**
     * Revoke the current authenticated user access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
