<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = ['user_id', 'booking_slot_id', 'booking_date', 'status'];

    protected function casts(): array {
        return [
            'booking_date' => 'date',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function bookingSlot(): BelongsTo { return $this->belongsTo(BookingSlot::class); }
}
