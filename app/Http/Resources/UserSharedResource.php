<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSharedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roles = $this->getRoleNames()->values()->toArray();
        $permissions = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();

        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'username'            => $this->username,
            'office_designation'  => $this->office_designation,
            'roles'               => $roles,
            'primary_role'        => $roles[0] ?? null,
            'permissions'         => $permissions,
            'profile_photo_url'   => $this->profile_photo_url,
            'has_profile_photo'   => $this->hasProfilePhoto(),
        ];
    }
}
