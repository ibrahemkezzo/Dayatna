<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\EntityFilter;
use App\Models\Entity;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EntityService
{
    public function __construct(protected ImageService $imageService) {}

    public function getPaginatedEntities(EntityFilter $filter, int $perPage = 15): LengthAwarePaginator
    {
        return Entity::query()
            ->with(['category', 'primaryImage', 'contacts', 'workingHours', 'user'])
            ->filterUsing($filter)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create Entity and upgrade owner role to 'owner'
     */
    public function createEntity(array $data, User $authUser): Entity
    {
        return DB::transaction(function () use ($data, $authUser) {

            // إذا كان الآدمن هو من ينشئ وحدد مستخدم آخر، نعتمد الحقل المرسل، وإلا نعتمد الآيدي الخاص بالمستخدم الحالي
            if ($authUser->role === 'admin' && isset($data['user_id'])) {
                $data['user_id'] = (int) $data['user_id'];
            } else {
                $data['user_id'] = $authUser->id;
            }

            // 1. إنشاء الـ Entity
            $entity = Entity::create($data);

            // 🌟 التحديث الجديد: تحديث رول مالك الـ Entity ليصبح 'owner' إذا لم يكن 'admin'
            $owner = User::find($data['user_id']);
            if ($owner && $owner->role !== 'admin') {
                $owner->update(['role' => 'owner']);
            }

            // 2. إدخال ساعات العمل
            $entity->workingHours()->createMany($data['working_hours']);

            // 3. إدخال جهات الاتصال
            if (!empty($data['contacts'])) {
                $entity->contacts()->createMany($data['contacts']);
            }

            // 4. رفع الصور
            if (!empty($data['images'])) {
                $this->imageService->uploadMultiple($entity, $data['images'], 'entities');
            }

            return $entity->load(['category', 'contacts', 'workingHours', 'images', 'user']);
        });
    }

    /**
     * Update Entity and handle ownership transfer role upgrades
     */
    public function updateEntity(int $id, array $data, User $authUser): Entity
    {
        return DB::transaction(function () use ($id, $data, $authUser) {
            $entity = Entity::findOrFail($id);

            if (!$authUser->role === 'admin' || !isset($data['user_id'])) {
                unset($data['user_id']);
            } else {
                $data['user_id'] = (int) $data['user_id'];
            }

            $entity->update($data);

            // 🌟 التحديث الجديد: في حال قام الآدمن بنقل الملكية لمستخدم آخر، نقوم بترقية رول المالك الجديد
            if (isset($data['user_id'])) {
                $newOwner = User::find($data['user_id']);
                if ($newOwner && $newOwner->role !== 'admin') {
                    $newOwner->update(['role' => 'owner']);
                }
            }

            if (isset($data['working_hours'])) {
                $entity->workingHours()->delete();
                $entity->workingHours()->createMany($data['working_hours']);
            }

            if (isset($data['contacts'])) {
                $entity->contacts()->delete();
                $entity->contacts()->createMany($data['contacts']);
            }

            if (!empty($data['images'])) {
                $this->imageService->uploadMultiple($entity, $data['images'], 'entities');
            }

            return $entity->load(['category', 'contacts', 'workingHours', 'images', 'user']);
        });
    }

    public function deleteEntity(int $id): void
    {
        DB::transaction(function () use ($id) {
            $entity = Entity::findOrFail($id);

            $entity->workingHours()->delete();
            $entity->contacts()->delete();

            foreach ($entity->images as $image) {
                $this->imageService->delete($image);
            }

            $owner = User::findOrFail($entity->user->id);
            if ($owner && $owner->role !== 'admin') {
                $owner->update(['role' => 'customer']);
            }

            $entity->delete();
        });
    }
}
