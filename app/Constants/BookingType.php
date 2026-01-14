<?php

namespace App\Constants;

class BookingType
{

    public const TRIP = "trip";
    public const PACKAGE = "package";
    public const HOTEL = "hotel";

    public static function all(): array
    {
        return [
            self::TRIP,
            self::PACKAGE,
            self::HOTEL
        ];
    }
}
