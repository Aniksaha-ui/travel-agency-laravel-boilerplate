<?php

namespace App\Repository\Services\SMS;

use Twilio\Rest\Client;

class SMSService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
    }

    public function sendSMS($to, $message)
    {
        return $this->twilio->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);
    }
}
