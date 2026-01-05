<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;

    public function __construct()
    {
        $sid = 'ACcf8bc3012edadc6bec284f656e5a8a21';
        $token = '9b9ebc146dce902b4ecb9858d53ddeef';
        $this->client = new Client($sid, $token);
    }


    public function sendWhatsAppMessage($to, $message)
    {
        if (empty($to)) {
            \Log::error('Twilio Error: Recipient number is null');
            return false;
        }

        try {
            return $this->client->messages->create(
                'whatsapp:+919136211332',  // Hardcoded number for testing
                [
                    'from' => env('TWILIO_WHATSAPP_FROM'),
                    'body' => $message
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Twilio WhatsApp Error: '.$e->getMessage());
            return false;
        }
    }

}