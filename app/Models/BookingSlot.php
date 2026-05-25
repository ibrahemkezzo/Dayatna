<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingSlot extends Model
{
    protected $fillable = ['entity_id', 'day_of_week', 'start_time', 'end_time', 'price'];

    public function entity(): BelongsTo { return $this->belongsTo(Entity::class); }
    public function bookings(): HasMany { return $this->hasMany(Booking::class); }
}
