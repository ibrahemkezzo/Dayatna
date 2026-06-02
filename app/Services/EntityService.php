<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\EntityFilter;
use App\Models\Entity;
use App\Services\ImageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EntityService
{
    /**
     * EntityService constructor.
     */
    public function __construct(protected ImageService $imageService) {}

    public function getPaginatedEntities(EntityFilter $filter, int $perPage = 15): LengthAwarePaginator
    {
        return Entity::query()
            ->with(['category', 'primaryImage', 'contacts', 'workingHours'])
            ->filterUsing($filter)
            ->latest()
            ->paginate($perPage);
    }

    public function createEntity(array $data, ?int $userId = null): Entity
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['user_id'] = $userId;

            // 1. Instantiating the core entity profile
            $entity = Entity::create($data);

            // 2. Persistent storage for multi-day scheduling timeline
            $entity->workingHours()->createMany($data['working_hours']);

            // 3. Conditional insertion of metadata contact channels
            if (!empty($data['contacts'])) {
                $entity->contacts()->createMany($data['contacts']);
            }

            // 4. Processing multi-image batch uploads safely into 'entities' folder
            if (!empty($data['images'])) {
                $this->imageService->uploadMultiple($entity, $data['images'], 'entities');
            }

            return $entity->load(['category', 'contacts', 'workingHours', 'images']);
        });
    }

    public function updateEntity(int $id, array $data): Entity
    {
        return DB::transaction(function () use ($id, $data) {
            $entity = Entity::findOrFail($id);

            // Updating base model record rows
            $entity->update($data);

            // Refreshing working hours timelines safely if dispatched
            if (isset($data['working_hours'])) {
                $entity->workingHours()->delete();
                $entity->workingHours()->createMany($data['working_hours']);
            }

            // Replacing contact channel collections conditionally
            if (isset($data['contacts'])) {
                $entity->contacts()->delete();
                $entity->contacts()->createMany($data['contacts']);
            }

            // Appending fresh uploaded images dynamically to the gallery if present
            if (!empty($data['images'])) {
                $this->imageService->uploadMultiple($entity, $data['images'], 'entities');
            }

            return $entity->load(['category', 'contacts', 'workingHours', 'images']);
        });
    }

    public function deleteEntity(int $id): void
    {
        DB::transaction(function () use ($id) {
            $entity = Entity::findOrFail($id);

            // Unlinking relational dependencies
            $entity->workingHours()->delete();
            $entity->contacts()->delete();

            // Cascading file system storage deletions for physical images
            foreach ($entity->images as $image) {
                $this->imageService->delete($image);
            }

            // Finally, purge structural primary entity index row
            $entity->delete();
        });
    }
}
