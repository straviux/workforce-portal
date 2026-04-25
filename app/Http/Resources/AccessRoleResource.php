<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\SystemRoles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessRoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->permissions->pluck('name')->values(),
            'users_count' => User::query()->role($this->name)->count(),
            'is_system' => SystemRoles::isLocked($this->name),
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}