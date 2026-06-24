<?php

namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = 'pending';
    case PROPERTY_CUSTODIAN = 'propertyCustodian';
    case TO_ORDER = 'to_order';
    case PARTIALLY_RELEASED = 'partially_released';
    case RELEASED = 'released';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROPERTY_CUSTODIAN => 'Property Custodian',
            self::TO_ORDER => 'To Order',
            self::PARTIALLY_RELEASED => 'Partially Released',
            self::RELEASED => 'Released',
            self::REJECTED => 'Rejected',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
