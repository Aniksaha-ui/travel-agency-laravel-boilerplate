<?php

namespace App\Constants;


class TicketStatus
{

    const PENDING = 0;
    const PROCESSING = 1;
    const DECLINE = 2;
  

    public static function labels(): array
    {
        return [
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::DECLINE => 'Closed',
        ];
    }

    public static function value(){
        return ['pending','processing','closed'];
    }


}
