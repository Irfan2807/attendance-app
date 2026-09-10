<?php

namespace App\Enums;

enum Role: int
{
    case SuperAdmin = 1;
    case Manager = 2;
    case Staff = 3;

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Director / HR',
            self::Manager => 'Manager',
            self::Staff => 'Staff',
        };
    }

    public static function options(): array
    {
        return [
            self::SuperAdmin->value => self::SuperAdmin->label(),
            self::Manager->value => self::Manager->label(),
            self::Staff->value => self::Staff->label(),
        ];
    }
}

