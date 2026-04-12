<?php

namespace App\Constants;

class VisaPaymentStatus
{
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const FAILED = 'failed';
    public const CANCELLED = 'cancelled';

    public static function all(): array
    {
        return [
            self::PENDING,
            self::PAID,
            self::FAILED,
        ];
    }
}
