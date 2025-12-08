<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;

class AfricasTalkingService
{
    protected $sms;

    public function __construct()
    {
        $username = config('services.africastalking.username');
        $apiKey   = config('services.africastalking.api_key');
        
        $AT       = new AfricasTalking($username, $apiKey);
        $this->sms = $AT->sms();
    }

    public function sendSms($to, $message)
    {
        return $this->sms->send([
            'to'      => $to,
            'message' => $message,
            'from'    => config('services.africastalking.from'),
        ]);
    }
}
