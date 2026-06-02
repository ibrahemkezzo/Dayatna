<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    protected string $disk = 'public';

    /**
     * Store and register a single dynamic asset onto the specified model.
     */
    public function upload(Model $model, UploadedFile $file, string $folder, bool $isPrimary = false): Image
    {
        return DB::transaction(function () use ($model, $file, $folder, $isPrimary) {
            // If the incoming file is primary, reset any existing primary records for this exact instance
            if ($isPrimary) {
                $this->resetPrimaryStatus($model);
            }

            // Persistence layer execution on designated storage disk
            $path = $file->store($folder, $this->disk);

            // Database record instantiation via morph relationship
            return $model->images()->create([
                'path' => $path,
                'is_primary' => $isPrimary,
            ]);
        });
    }

    /**
     * Process bulk batch uploads for multi-image collections.
     */
    public function uploadMultiple(Model $model, array $files, string $folder): array
    {
        $uploadedImages = [];

        // Auto-detect if a primary image is missing to elect the first file implicitly
        $hasPrimary = $model->images()->where('is_primary', true)->exists();

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $isPrimary = (!$hasPrimary && $index === 0);
                $uploadedImages[] = $this->upload($model, $file, $folder, $isPrimary);
            }
        }

        return $uploadedImages;
    }

    /**
     * Delete both the physical storage file and its relational mapping entry.
     */
    public function delete(Image $image): bool
    {
        return DB::transaction(function () use ($image) {
            // Unlink structural storage file first to prevent dangling data waste
            if (Storage::disk($this->disk)->exists($image->path)) {
                Storage::disk($this->disk)->delete($image->path);
            }

            $wasPrimary = $image->is_primary;
            $imageable = $image->imageable;

            $deleted = (bool) $image->delete();

            // Self-healing fallback: promote the oldest remaining asset if the active primary was eliminated
            if ($deleted && $wasPrimary && $imageable) {
                $nextImage = $imageable->images()->first();
                if ($nextImage) {
                    $nextImage->update(['is_primary' => true]);
                }
            }

            return $deleted;
        });
    }

    /**
     * Promote an existing asset record to become the main primary entry.
     */
    public function setAsPrimary(Image $image): void
    {
        DB::transaction(function () use ($image) {
            if ($image->imageable) {
                $this->resetPrimaryStatus($image->imageable);
            }
            $image->update(['is_primary' => true]);
        });
    }

    /**
     * Reset active main asset flags across the specific model instance scope.
     */
    private function resetPrimaryStatus(Model $model): void
    {
        $model->images()->where('is_primary', true)->update(['is_primary' => false]);
    }
}
