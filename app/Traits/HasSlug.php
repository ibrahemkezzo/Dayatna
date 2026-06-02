<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the HasSlug trait for the model.
     * Hooks into creating and updating Eloquent events.
     */
    protected static function bootHasSlug(): void
    {
        // dd('Event is firing inside Seeder for model: ' . get_class($model));

        static::creating(function (Model $model) {
            $model->slug = static::generateUniqueSlug($model);
        });

        static::updating(function (Model $model) {
            // Only regenerate slug if the source field (e.g., name) has changed
            $sourceField = $model->slugSourceField();
            if ($model->isDirty($sourceField)) {
                $model->slug = static::generateUniqueSlug($model);
            }
        });
    }

    /**
     * Determine the source column for slug generation.
     * Overwrite this method in any model if the source is not 'name'.
     */
    public function slugSourceField(): string
    {
        return 'name';
    }

    /**
     * Generate a unique slug based on the model's table data.
     */
    protected static function generateUniqueSlug(Model $model): string
    {
        $sourceField = $model->slugSourceField();
        $sourceValue = $model->{$sourceField} ?? '';

        $slug = Str::slug($sourceValue);

        // Fallback fallback for Arabic text where Str::slug returns empty
        if (empty($slug)) {
            $slug = str_replace(' ', '-', trim($sourceValue));
        }

        // Query the specific model's table to ensure uniqueness, ignoring the current record ID on update
        $count = static::where('slug', 'LIKE', "{$slug}%")
            ->where($model->getKeyName(), '!=', $model->getKey() ?? 0)
            ->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
}
