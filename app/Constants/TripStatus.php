<?php

namespace App\Constants;


class TripStatus
{

    const INACTIVE = 0;
    const ACTIVE = 1;
  

    public static function labels(): array
    {
        return [
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        ];
    }

    public static function value(){
        return ['pending','resolved','rejected'];
    }


}
