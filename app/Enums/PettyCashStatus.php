<?php

namespace App\Enums;

enum PettyCashStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case AUDITED = 'audited';
    case RETURNED = 'returned';
    case RELEASED = 'released';
    case FOR_LIQUIDATION = 'for_liquidation';
    case APPROVED_LIQUIDATION = 'approved_liquidation';
    case REQUESTED = 'requested';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::AUDITED => 'Audited',
            self::RETURNED => 'Returned',
            self::RELEASED => 'Released',
            self::FOR_LIQUIDATION => 'For Liquidation',
            self::APPROVED_LIQUIDATION => 'Approved Liquidation',
            self::REQUESTED => 'Requested',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
