<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkingHourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'day_of_week' => $this->day_of_week,
            'is_closed' => $this->is_closed,
            // تنسيق الوقت ليظهر بشكل مناسب hh:mm أو إرجاع null إذا كان مغلقاً
            'open_time' => $this->is_closed ? null : ($this->open_time ? substr($this->open_time, 0, 5) : null),
            'close_time' => $this->is_closed ? null : ($this->close_time ? substr($this->close_time, 0, 5) : null),
        ];
    }
}
