<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PENDING = 'pending';
    case AUDITED = 'audited';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::PENDING => 'Pending',
            self::AUDITED => 'Audited',
            self::RETURNED => 'Returned',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
