<?php

namespace App\Constants;

class NotificationStatus
{

    public const ACTIVE = 1;
    public const INACTIVE = 0;

    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::INACTIVE
        ];
    }
}
