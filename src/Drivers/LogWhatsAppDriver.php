<?php

namespace Devspinotek\NotificationControl\Drivers;

use Devspinotek\NotificationControl\Contracts\WhatsAppDriverInterface;
use Illuminate\Support\Facades\Log;

class LogWhatsAppDriver implements WhatsAppDriverInterface
{
    public function send(string $phone, string $message, array $context = []): bool
    {
        Log::info('[NotificationControl][WhatsApp] Simulated message dispatch', [
            'phone' => $phone,
            'message' => $message,
            'context' => $context,
        ]);

        return true;
    }
}

