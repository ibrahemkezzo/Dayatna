<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerTime extends Model
{
    protected $fillable = ['date', 'fajr', 'shorouk', 'dhuhr', 'asr', 'maghrib', 'isha'];

    protected function casts(): array {
        return [
            'date' => 'date',
        ];
    }
}
