<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Filters\CategoryFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    /**
     * Fetch Categories
     *
     * Get a paginated list of all system categories. Supports filtering.
     * @queryParam search string Filter categories by name. Example: Football
     * @queryParam main_only boolean Retrieve only top-level categories. Example: true
     * @queryParam parent_id integer Filter subcategories belonging to a parent ID. Example: 1
     */
    public function index(Request $request, CategoryFilter $filter): JsonResponse
    {
        $categories = $this->categoryService->getPaginatedCategories($filter, (int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Categories retrieved successfully.',
            'meta'    => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'total'        => $categories->total(),
            ],
            'data'    => CategoryResource::collection($categories)
        ], Response::HTTP_OK);
    }

    /**
     * Create Category
     * * @authenticated
     * @bodyParam image file Description of the category image asset.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return response()->json([
            'message' => 'Category created successfully.',
            'data'    => new CategoryResource($category)
        ], Response::HTTP_CREATED);
    }

    /**
     * View Category Details
     * @urlParam id integer required The category ID. Example: 1
     */
    public function show(int $id): JsonResponse
    {
        $category = \App\Models\Category::with(['parent', 'subcategories', 'image'])->findOrFail($id);

        return response()->json([
            'data' => new CategoryResource($category)
        ], Response::HTTP_OK);
    }

    /**
     * Update Category
     * * @authenticated
     * @urlParam id integer required The category ID. Example: 1
     * @bodyParam image file Description of the category image asset.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->categoryService->updateCategory($id, $request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'data'    => new CategoryResource($category)
        ], Response::HTTP_OK);
    }

    /**
     * Delete Category
     * * @authenticated
     * @urlParam id integer required The category ID. Example: 1
     */
    public function destroy(int $id): JsonResponse
    {
        $this->categoryService->deleteCategory($id);

        return response()->json([
            'message' => 'Category deleted successfully.'
        ], Response::HTTP_OK);
    }
}
