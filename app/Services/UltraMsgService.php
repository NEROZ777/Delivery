<?php

namespace App\Services;

use UltraMsg\WhatsAppApi;

class UltraMsgService
{
    protected $instanceId;
    protected $token;

    public function __construct()
    {
        $this->instanceId = env('ULTRAMSG_INSTANCE_ID');
        $this->token = config('ultramsg.token');

        // dd('Instance ID:', $this->instanceId, 'Token:', $this->token);

        if (!$this->instanceId || !$this->token) {
            throw new \Exception('Instance ID or Token is not configured properly.');
        }
    }

    public function sendMessage($to, $message)
    {
        // if (empty($to) || empty($message)) {
        //     throw new \InvalidArgumentException('Recipient number or message cannot be empty.');
        // }

        try {
            // dd($this->token);
            $ultramsg = new WhatsAppApi($this->token, $this->instanceId);
          
            return $ultramsg->sendChatMessage($to, $message);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    
}
