<?php

namespace App\Constants;

class ApiResponseStatus
{

    public const SUCCESS = "SUCCESS";
    public const FAILED = "FAILED";

    public static function all(): array
    {
        return [
            self::SUCCESS,
            self::FAILED
        ];
    }
}
