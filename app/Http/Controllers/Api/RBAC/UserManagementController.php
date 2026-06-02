<?php

namespace App\Http\Controllers\Api\RBAC;

use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\UpdateUserRequest;
use App\Http\Resources\UserManagementResource;
use App\Services\RBAC\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserManagementController extends Controller
{
    /**
     * UserManagementController constructor.
     */
    public function __construct(
        protected UserManagementService $userService
    ) {}

    /**
     * List Users
     * Fetch all system accounts paginated with their security matrix.
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->getPaginatedUsers();

        return response()->json([
            'message' => 'System registry users accounts fetched successfully.',
            'meta'    => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
            'data'    => UserManagementResource::collection($users)
        ], Response::HTTP_OK);
    }

    /**
     * Create User Account
     * Inject a new user registry record with a predefined functional security tier.
     */
    public function store(\App\Http\Requests\RBAC\StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'message' => 'User account spawned and designated role assigned successfully.',
            'data'    => new UserManagementResource($user)
        ], Response::HTTP_CREATED);
    }

    /**
     * Show User Profile
     * Grab singular profile account records mapping all access points.
     * @urlParam id integer required The user record ID. Example: 1
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        return response()->json([
            'data' => new UserManagementResource($user)
        ], Response::HTTP_OK);
    }

    /**
     * Update User Access
     * Modify basic bio attributes, account state fields, or assignable authorization tiers.
     * @urlParam id integer required The user record ID. Example: 1
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->updateUser($id, $request->validated());

        return response()->json([
            'message' => 'User account modifications saved successfully.',
            'data'    => new UserManagementResource($user)
        ], Response::HTTP_OK);
    }

    /**
     * Terminate User Account
     * Purge user profile structure entirely from the master database nodes.
     * @urlParam id integer required The user record ID. Example: 1
     */
    public function destroy(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return response()->json([
            'message' => 'User registry row and linked properties truncated permanently.'
        ], Response::HTTP_OK);
    }
}
