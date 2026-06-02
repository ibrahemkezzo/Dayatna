<?php

namespace App\Models;

use App\Traits\HasImages;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Category extends Model
{
    use HasSlug , HasImages;
    protected $fillable = ['name', 'slug', 'parent_id', 'icon'];

    public function entities(): HasMany {
        return $this->hasMany(Entity::class);
    }

    public function subcategories(): HasMany {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    /**
     * Category strict single image representation constraint wrapper.
     * mapped out via the standard MorphOne targeting the master image system.
     */
    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')->latestOfMany();
    }

    public function scopeFilterUsing($query, $filter) {
        return $filter->apply($query);
    }
}
