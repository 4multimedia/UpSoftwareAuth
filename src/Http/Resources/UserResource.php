<?php

namespace Upsoftware\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function getRoles() {
        if (config('upsoftware.tenancy')) {
            return $this->rolesByTenant();
        } else {
            return $this->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name
            ]);
        }

    }


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hash,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'roles' => $this->getRoles()
        ];
    }
}
