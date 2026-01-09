<?php

namespace App\Constants;

class BookingStatus
{

    public const PAID = "paid";
    public const CANCELLED = "cancelled";

    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::INACTIVE
        ];
    }
}
