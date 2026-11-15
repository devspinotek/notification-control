<?php

namespace Devspinotek\NotificationControl\Contracts;

interface WhatsAppDriverInterface
{
    public function send(string $phone, string $message, array $context = []): bool;
}

