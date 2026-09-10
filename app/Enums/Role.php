<?php

namespace App\Enums;

enum Role: int
{
    case SuperAdmin = 1;
    case Manager = 2;
    case Staff = 3;
    case HR = 4;

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Director',
            self::Manager => 'Manager',
            self::Staff => 'Staff',
            self::HR => 'HR Executive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Manager => 'warning',
            self::Staff => 'success',
            self::HR => 'info',
        };
    }

    public static function options(): array
    {
        return [
            self::SuperAdmin->value => self::SuperAdmin->label(),
            self::HR->value => self::HR->label(),
            self::Manager->value => self::Manager->label(),
            self::Staff->value => self::Staff->label(),
        ];
    }
}

