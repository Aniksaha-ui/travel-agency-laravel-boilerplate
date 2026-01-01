<?php

namespace App\Constants;


class TicketStatus
{

    const PENDING = 0;
    const RESOLVED = 1;
    const DECLINE = 2;
  

    public static function labels(): array
    {
        return [
            self::PENDING => 'Pending',
            self::RESOLVED => 'Resolved',
            self::DECLINE => 'Declined',
        ];
    }

    public static function value(){
        return ['pending','resolved','rejected'];
    }


}
