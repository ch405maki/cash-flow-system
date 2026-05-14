<?php

namespace App\Enums;

enum RequestToOrderStatus: string
{
    case PENDING = 'pending';
    case FOR_EOD = 'forEOD';
    case FOR_PO = 'forPO';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::FOR_EOD => 'For EOD',
            self::FOR_PO => 'For PO',
            self::COMPLETED => 'Completed',
            self::REJECTED => 'Rejected',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
