<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Filters\EntityFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\StoreEntityRequest;
use App\Http\Requests\Entity\UpdateEntityRequest;
use App\Http\Resources\EntityResource;
use App\Models\Entity;
use App\Services\EntityService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response; // Using standardized system constants

class EntityController extends Controller
{
    /**
     * EntityController constructor.
     */
    public function __construct(protected EntityService $entityService) {}

    public function index(EntityFilter $filter): JsonResponse
    {
        $entities = $this->entityService->getPaginatedEntities($filter);
        return response()->json([
            'message' => 'Entities retrieved successfully.',
            'meta'     => [
                'current_page' => $entities->currentPage(),
                'last_page'    => $entities->lastPage(),
                'total'        => $entities->total(),
            ],
            'data'    => EntityResource::collection($entities)
        ], Response::HTTP_OK);
    }

    public function store(StoreEntityRequest $request): JsonResponse
    {
        $entity = $this->entityService->createEntity($request->validated(), auth()->id());

        return response()->json([
            'message' => 'Entity created successfully with its structural timeline and media.',
            'data' => new EntityResource($entity)
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $entity = Entity::with(['category', 'contacts', 'workingHours', 'images'])->findOrFail($id);
        return response()->json([
            'data' => new EntityResource($entity)
        ], Response::HTTP_OK);
    }

    public function update(UpdateEntityRequest $request, int $id): JsonResponse
    {
        $entity = $this->entityService->updateEntity($id, $request->validated());

        return response()->json([
            'message' => 'Entity updated successfully with additional media.',
            'data' => new EntityResource($entity)
        ], Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->entityService->deleteEntity($id);

        return response()->json([
            'message' => 'Entity and all its related configurations and assets deleted successfully.'
        ], Response::HTTP_OK);
    }
}
