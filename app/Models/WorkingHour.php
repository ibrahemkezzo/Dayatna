<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $fillable = ['entity_id', 'day_of_week', 'open_time', 'close_time', 'is_closed'];

    protected function casts(): array {
        return [
            'is_closed' => 'boolean',
            'day_of_week' => 'integer',
        ];
    }

     public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
