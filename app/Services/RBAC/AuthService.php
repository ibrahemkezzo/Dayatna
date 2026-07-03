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
     * * @param array $data
     * @param User|null $authUser الكائن الخاص بالمستخدم الحالي الذي ينفذ الطلب (إن وجد)
     * @return array
     */
    public function register(array $data, ?User $authUser = null): array
    {
        // 🌟 شرط الأمان: افتراضياً الرول هو customer، ولا يسمح بتعديله إلا إذا كان المنشئ Admin


        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'password' => $data['password'], // يتم التشفير تلقائياً عبر الـ Casts بداخل المودل
            'role'     => 'customer',
        ]);
        $user->assignRole('customer'); // 🌟 تعيين الدور الافتراضي في Spatie

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
