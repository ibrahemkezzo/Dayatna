<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'icon'          => $this->icon,
            'parent_id'     => $this->parent_id,

            // Utilizing the standard unified Image model url attribute accessor safely
            'image_url'     => $this->relationLoaded('image') && $this->image
                ? $this->image->url
                : null,

            // Load relations conditionally only if they are eager loaded
            'parent'        => new self($this->whenLoaded('parent')),
            'subcategories' => self::collection($this->whenLoaded('subcategories')),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
