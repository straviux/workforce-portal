<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManagedUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'roles' => $this->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])->values(),
            'role_names' => $this->getRoleNames()->values(),
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}