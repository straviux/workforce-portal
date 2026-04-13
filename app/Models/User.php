<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'office_designation',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo ? asset('storage/' . $this->profile_photo) : null;
    }

    public function hasProfilePhoto(): bool
    {
        return !empty($this->profile_photo);
    }

    /**
     * Get all permissions via assigned roles only (no direct user permissions).
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions)
            ->unique('id')
            ->values();
    }

    /**
     * Check if user has a specific permission (via roles).
     */
    public function hasPermission(string $permission): bool
    {
        return $this->getAllPermissions()->pluck('name')->contains($permission);
    }

    /**
     * Direct user permissions are not used — always returns false.
     */
    public function hasDirectPermission($permission, $guardName = null): bool
    {
        return false;
    }
}
