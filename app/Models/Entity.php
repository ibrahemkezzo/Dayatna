<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Entity extends Model
{
    protected $fillable = ['category_id', 'user_id', 'name', 'description', 'address', 'latitude', 'longitude', 'price_range', 'is_verified', 'status'];

    protected function casts(): array {
        return [
            'is_verified' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function workingHours(): HasMany { return $this->hasMany(WorkingHour::class); }

    // العلاقات متعددة الأشكال Polymorphic
    public function contacts(): MorphMany { return $this->morphMany(Contact::class, 'contactable'); }
    public function images(): MorphMany { return $this->morphMany(Image::class, 'imageable'); }

    // جلب الصورة الرئيسية فقط بكفاءة عالية
    public function primaryImage(): MorphOne {
        return $this->morphOne(Image::class, 'imageable')->where('is_primary', true);
    }

    public function bookingSlots(): HasMany { return $this->hasMany(BookingSlot::class); }
    public function offers(): HasMany { return $this->hasMany(Offer::class); }
}
