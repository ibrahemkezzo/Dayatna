<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\EntityFilter;
use App\Models\Entity;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

            // تحديث رول مالك الـ Entity ليصبح 'owner' بداخل قاعدة البيانات وحزمة Spatie
            $owner = User::find($data['user_id']);
            if ($owner && $owner->role !== 'admin') {
                $owner->update(['role' => 'owner']);
                $owner->syncRoles(['owner']); // 🌟 تحديث Spatie (يمسح الأدوار السابقة ويثبت دور واحد فقط)
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
     * Update Entity with explicit multi-level authorization and role management
     */
    public function updateEntity(int $id, array $data, User $authUser): Entity
    {
        return DB::transaction(function () use ($id, $data, $authUser) {
            $entity = Entity::findOrFail($id);

            $isAdmin = $authUser->role === 'admin';
            $isOwner = $entity->user_id === $authUser->id;

            // 🛡️ الحالة الثانية: التحقق من أن القائم بالتعديل إما Admin أو مالك الـ Entity نفسه لباقي البيانات
            if (!$isAdmin && !$isOwner) {
                throw new AccessDeniedHttpException('You are not authorized to update this entity.');
            }

            // 🛡️ الحالة الأولى: محاولة تعديل الـ user_id (نقل الملكية)
            if (isset($data['user_id'])) {
                $newUserId = (int) $data['user_id'];

                if ($newUserId !== $entity->user_id) {
                    // إذا لم يكن آدمن، نمنعه تماماً حتى لو كان هو المالك الحالي للـ entity
                    if (!$isAdmin) {
                        throw new AccessDeniedHttpException('Only administrators can change the entity owner.');
                    }

                    // 🌟 الحالة الثالثة: نقل الملكية من قبل الآدمن -> إدارة الأدوار (Roles)
                    $oldOwner = User::find($entity->user_id);
                    $newOwner = User::find($newUserId);

                    // 1. إرجاع المالك القديم إلى customer (بشرط ألا يكون آدمن، وألا يملك منشآت أخرى في النظام)
                    if ($oldOwner && $oldOwner->role !== 'admin') {
                        $hasOtherEntities = Entity::where('user_id', $oldOwner->id)->where('id', '!=', $entity->id)->exists();
                        if (!$hasOtherEntities) {
                            $oldOwner->update(['role' => 'customer']);
                            $oldOwner->syncRoles(['customer']); // 🌟 تحديث Spatie للمالك القديم
                        }
                    }

                    // 2. ترقية المالك الجديد إلى owner (إذا لم يكن آدمن بالفعل)
                    if ($newOwner && $newOwner->role !== 'admin') {
                        $newOwner->update(['role' => 'owner']);
                        $newOwner->syncRoles(['owner']); // 🌟 تحديث Spatie للمالك الجديد
                    }

                    // اعتماد الـ user_id الجديد في مصفوفة التحديث
                    $data['user_id'] = $newUserId;
                }
            } else {
                // لحماية البيانات في حال لم يتم إرسال الحقل أو تم إرساله مصفوفة فارغة
                unset($data['user_id']);
            }

            // تحديث البيانات الأساسية للـ Entity
            $entity->update($data);

            // تحديث ساعات العمل بمرونة إذا تم إرسالها بالطلب
            if (isset($data['working_hours'])) {
                $entity->workingHours()->delete();
                $entity->workingHours()->createMany($data['working_hours']);
            }

            // تحديث جهات الاتصال بمرونة إذا تم إرسالها بالطلب
            if (isset($data['contacts'])) {
                $entity->contacts()->delete();
                $entity->contacts()->createMany($data['contacts']);
            }

            // معالجة الصور الإضافية المرفوعة
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

            // عند الحذف، نتحقق إذا كان المستخدم لا يملك أي entity أخرى بالنظام قبل إرجاعه لـ customer
            $owner = User::find($entity->user_id);
            if ($owner && $owner->role !== 'admin') {
                $hasOtherEntities = Entity::where('user_id', $owner->id)->where('id', '!=', $entity->id)->exists();
                if (!$hasOtherEntities) {
                    $owner->update(['role' => 'customer']);
                    $owner->syncRoles(['customer']); // 🌟 تحديث Spatie عند حذف الـ Entity الأخيرة للمستخدم
                }
            }

            $entity->delete();
        });
    }
}
