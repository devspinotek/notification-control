<?php

namespace Devspinotek\NotificationControl\Support;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Cache;

class NotificationActivityRecorder
{
    public function recordMessageSent(MessageSent $event): void
    {
        if (! config('notification-control.activity_log.enabled', true)) {
            return;
        }

        $addresses = collect($event->message->getTo())->map(fn ($addr) => $addr->getAddress())->implode(', ');

        $this->push([
            'channel' => 'mail',
            'name' => $event->data['__laravel_notification'] ?? ($event->message->getSubject() ?: 'Email'),
            'recipient' => $addresses ?: '-',
            'subject' => $event->message->getSubject() ?: '-',
            'mode' => NotificationDispatcher::mode(),
            'sent_at' => now()->toDateTimeString(),
        ]);
    }

    public function recordNotificationSent(NotificationSent $event): void
    {
        if (! config('notification-control.activity_log.enabled', true)) {
            return;
        }

        $recipient = method_exists($event->notifiable, 'getAttribute')
            ? ($event->notifiable->getAttribute('email') ?? (string) $event->notifiable->getKey())
            : get_class($event->notifiable);

        $this->push([
            'channel' => $event->channel,
            'name' => class_basename($event->notification),
            'recipient' => $recipient ?: '-',
            'subject' => '-',
            'mode' => NotificationDispatcher::mode(),
            'sent_at' => now()->toDateTimeString(),
        ]);
    }

    public function recent(): array
    {
        $key = (string) config('notification-control.activity_log.cache_key', 'notification_control.recent_activity');

        return Cache::get($key, []);
    }

    public function clear(): void
    {
        $key = (string) config('notification-control.activity_log.cache_key', 'notification_control.recent_activity');
        Cache::forget($key);
    }

    private function push(array $entry): void
    {
        $key = (string) config('notification-control.activity_log.cache_key', 'notification_control.recent_activity');
        $max = max(1, (int) config('notification-control.activity_log.max_entries', 50));

        $entries = Cache::get($key, []);
        array_unshift($entries, $entry);
        $entries = array_slice($entries, 0, $max);

        Cache::forever($key, $entries);
    }
}

