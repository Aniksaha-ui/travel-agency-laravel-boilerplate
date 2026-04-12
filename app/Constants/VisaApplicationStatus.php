<?php

namespace App\Constants;

class VisaApplicationStatus
{
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const UNDER_REVIEW = 'under_review';
    public const DOCUMENT_PENDING = 'document_pending';
    public const PROCESSING = 'processing';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    // Legacy aliases kept for the earlier in-progress visa module.
    public const PENDING = self::SUBMITTED;
    public const SENT_TO_EMBASSY = 'sent_to_embassy';

    public static function all(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::UNDER_REVIEW,
            self::DOCUMENT_PENDING,
            self::PROCESSING,
            self::APPROVED,
            self::REJECTED,
        ];
    }

    public static function userEditable(): array
    {
        return [
            self::DRAFT,
            self::DOCUMENT_PENDING,
        ];
    }

    public static function userDeletable(): array
    {
        return [
            self::DRAFT,
        ];
    }

    public static function submittable(): array
    {
        return [
            self::DRAFT,
            self::DOCUMENT_PENDING,
        ];
    }

    public static function payable(): array
    {
        return [
            self::SUBMITTED,
            self::UNDER_REVIEW,
            self::DOCUMENT_PENDING,
            self::PROCESSING,
            self::APPROVED,
        ];
    }

    public static function finalStatuses(): array
    {
        return [
            self::APPROVED,
            self::REJECTED,
        ];
    }
}
