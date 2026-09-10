<?php

namespace App\Enums;

enum LeaveType: string
{
    case MedicalLeave = 'mc';
    case AnnualLeave = 'annual';
    case EmergencyLeave = 'emergency';
    case Hospitalization = 'hospitalization';
    case UnpaidLeave = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::MedicalLeave => 'Medical Leave (MC)',
            self::AnnualLeave => 'Annual Leave',
            self::EmergencyLeave => 'Emergency Leave',
            self::Hospitalization => 'Hospitalization',
            self::UnpaidLeave => 'Unpaid Leave',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MedicalLeave => 'danger',
            self::AnnualLeave => 'primary',
            self::EmergencyLeave => 'warning',
            self::Hospitalization => 'danger',
            self::UnpaidLeave => 'gray',
        };
    }

    public function requiresAttachment(): bool
    {
        return match ($this) {
            self::MedicalLeave, self::Hospitalization => true,
            default => false,
        };
    }

    public static function options(): array
    {
        return [
            self::MedicalLeave->value => self::MedicalLeave->label(),
            self::AnnualLeave->value => self::AnnualLeave->label(),
            self::EmergencyLeave->value => self::EmergencyLeave->label(),
            self::Hospitalization->value => self::Hospitalization->label(),
            self::UnpaidLeave->value => self::UnpaidLeave->label(),
        ];
    }
}

