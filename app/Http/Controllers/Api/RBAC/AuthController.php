<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\RBAC;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\RBAC\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * User Registration
     * Register a new citizen or provider account and return an access token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        [$user, $token] = $this->authService->register($request->validated(), auth()->user());

        return response()->json([
            'message' => 'Account created successfully.',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token,
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * User Login
     * Validate phone and password to retrieve a valid authentication token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        [$user, $token] = $this->authService->login($request->validated());

        return response()->json([
            'message' => 'Login successful.',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token,
            ]
        ], Response::HTTP_OK);
    }

    /**
     * User Logout
     * Revoke the current authenticated session token safely.
     * @authenticated
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully and token revoked.'
        ], Response::HTTP_OK);
    }
}
