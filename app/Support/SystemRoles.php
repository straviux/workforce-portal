<?php

namespace App\Support;

class SystemRoles
{
    public const NAMES = [
        'admin',
        'staff',
    ];

    public static function isLocked(string $name): bool
    {
        return in_array($name, self::NAMES, true);
    }
}