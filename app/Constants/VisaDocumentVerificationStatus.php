<?php

namespace App\Constants;

class VisaDocumentVerificationStatus
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public static function all(): array
    {
        return [
            self::PENDING,
            self::APPROVED,
            self::REJECTED,
        ];
    }
}
