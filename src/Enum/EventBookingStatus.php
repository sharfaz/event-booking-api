<?php

namespace App\Enum;

enum EventBookingStatus: string
{
    case STATUS_PENDING = 'pending';
    case STATUS_CONFIRMED = 'confirmed';
    case STATUS_CANCELLED = 'cancelled';
}
