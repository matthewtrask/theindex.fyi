<?php

namespace App\Enums;

enum IndexStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Dead = 'dead';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Dead => 'Dead',
        };
    }
}
