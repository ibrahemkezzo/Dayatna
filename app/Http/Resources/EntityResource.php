<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'price_range' => $this->price_range,
            'is_verified' => $this->is_verified,
            'status' => $this->status,

            // Conditionally mapped relation structures
            'category' => $this->relationLoaded('category') ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,

            // Quick extraction of the main primary view image path
            'primary_image' => $this->relationLoaded('primaryImage') && $this->primaryImage
                ? $this->primaryImage->url
                : null,

            // Full operational media gallery collection mapping
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->url,
                    'is_primary' => $image->is_primary,
                ]);
            }),

            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),

            // Sequential sorting execution of working schedules by daily index (0-6)
            'working_hours' => WorkingHourResource::collection(
                $this->whenLoaded('workingHours', function() {
                    return $this->workingHours->sortBy('day_of_week');
                })
            ),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
