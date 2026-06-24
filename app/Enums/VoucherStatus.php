<?php

namespace App\Enums;

enum VoucherStatus: string
{
    case DRAFT = 'draft';
    case FOR_AUDIT = 'forAudit';
    case FOR_CHECK = 'forCheck';
    case RETURN = 'return';
    case REJECTED = 'rejected';
    case UNRELEASED = 'unreleased';
    case RELEASED = 'released';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::FOR_AUDIT => 'For Audit',
            self::FOR_CHECK => 'For Check',
            self::RETURN => 'Return',
            self::REJECTED => 'Rejected',
            self::UNRELEASED => 'Unreleased',
            self::RELEASED => 'Released',
            self::COMPLETED => 'Completed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
