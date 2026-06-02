<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\CategoryFilter;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * CategoryService constructor.
     */
    public function __construct(
        protected ImageService $imageService
    ) {}

    public function getPaginatedCategories(CategoryFilter $filter, int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->with(['parent', 'subcategories', 'image']) // Eager load the singular image relationship
            ->withCount('subcategories')
            ->filterUsing($filter)
            ->paginate($perPage);
    }

    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $imageFile = $data['image'] ?? null;
            if ($imageFile) {
                unset($data['image']);
            }

            $category = Category::create($data);

            // Execute using the standardized ImageService component
            if ($imageFile) {
                $this->imageService->upload($category, $imageFile, 'categories', true);
            }

            return $category->load('image');
        });
    }

    public function updateCategory(int $id, array $data): Category
    {
        return DB::transaction(function () use ($id, $data) {
            $category = Category::findOrFail($id);

            $imageFile = $data['image'] ?? null;
            unset($data['image']);

            $category->update($data);

            // Constraint Check: Enforce single-image isolation pattern by purging old entity asset
            if ($imageFile) {
                $oldImage = $category->image;
                if ($oldImage) {
                    $this->imageService->delete($oldImage);
                }

                // Upload the replacement asset safely as primary
                $this->imageService->upload($category, $imageFile, 'categories', true);
            }

            return $category->load('image');
        });
    }

    public function deleteCategory(int $id): void
    {
        DB::transaction(function () use ($id) {
            $category = Category::findOrFail($id);

            // Purge the single isolated asset systematically if present during row termination
            $oldImage = $category->image;
            if ($oldImage) {
                $this->imageService->delete($oldImage);
            }

            $category->delete();
        });
    }
}
