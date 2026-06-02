<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserManagementResource extends JsonResource
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
            'email'         => $this->email,
            'phone'         => $this->phone,
            'is_active'     => $this->is_active,
            'is_searchable' => $this->is_searchable,

            // Extract core Spatie roles names array safely
            'roles' => $this->relationLoaded('roles')
                ? $this->roles->pluck('name')
                : [],

            // Extract explicit direct non-inherited permissions
            'direct_permissions' => $this->relationLoaded('permissions')
                ? $this->permissions->pluck('name')
                : [],

            // Convenient array aggregate of inherited and custom permission values
            'all_permissions' => $this->relationLoaded('roles') || $this->relationLoaded('permissions')
                ? $this->getAllPermissions()->pluck('name')->unique()->values()
                : [],

            'contacts'   => ContactResource::collection($this->whenLoaded('contacts')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
