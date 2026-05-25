<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Offer extends Model
{
protected $fillable = ['entity_id', 'title', 'description', 'image', 'start_date', 'end_date', 'is_active'];

    protected function casts(): array {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function entity(): BelongsTo { return $this->belongsTo(Entity::class); }
    public function images(): MorphMany { return $this->morphMany(Image::class, 'imageable'); }
}
