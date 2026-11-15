<?php

namespace Devspinotek\NotificationControl\Support;

use Devspinotek\NotificationControl\Contracts\WhatsAppDriverInterface;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

use function Illuminate\Support\defer;

class NotificationDispatcher
{
    public static function mode(?string $override = null): string
    {
        if ($override !== null) {
            $mode = strtolower($override);

            return in_array($mode, ['async', 'sync', 'now'], true) ? $mode : 'async';
        }

        $mode = strtolower((string) (config('mail.dispatch_mode') ?? config('notification-control.dispatch.mode', 'async')));

        return in_array($mode, ['async', 'sync', 'now'], true) ? $mode : 'async';
    }

    public static function isSync(?string $override = null): bool
    {
        return self::mode($override) === 'sync';
    }

    public static function resolveEmailRecipient(mixed $to): mixed
    {
        $sandboxEmail = config('notification-control.dispatch.sandbox.redirect_email_to');

        if (filled($sandboxEmail) && ! app()->isProduction()) {
            return $sandboxEmail;
        }

        return $to;
    }

    public static function activeChannels(string $notificationKey, array $defaultChannels = ['mail']): array
    {
        $store = app(ChannelPolicyStore::class);

        return array_values(array_filter($defaultChannels, function (string $channel) use ($store, $notificationKey, $defaultChannels) {
            return $store->isChannelEnabled($notificationKey, $channel, $defaultChannels);
        }));
    }

    public static function send(mixed $to, Mailable $mailable, ?string $forceMode = null, ?string $notificationKey = null): void
    {
        if ($notificationKey !== null) {
            $active = self::activeChannels($notificationKey, ['mail']);
            if (! in_array('mail', $active, true)) {
                return;
            }
        }

        $target = self::resolveEmailRecipient($to);
        $mode = self::mode($forceMode);

        if ($mode === 'now') {
            Mail::to($target)->send($mailable);

            return;
        }

        if ($mode === 'sync') {
            defer(fn () => Mail::to($target)->send($mailable));

            return;
        }

        Mail::to($target)->queue($mailable);
    }

    public static function notify(mixed $notifiable, Notification $notification, ?string $forceMode = null): void
    {
        $mode = self::mode($forceMode);

        if ($mode === 'now') {
            $notifiable->notifyNow($notification);

            return;
        }

        if ($mode === 'sync') {
            defer(fn () => $notifiable->notifyNow($notification));

            return;
        }

        $notifiable->notify($notification);
    }

    public static function sendWhatsApp(string $phone, string $message, array $context = [], ?string $notificationKey = null): bool
    {
        if ($notificationKey !== null) {
            $active = self::activeChannels($notificationKey, ['whatsapp']);
            if (! in_array('whatsapp', $active, true)) {
                return false;
            }
        }

        return app(WhatsAppDriverInterface::class)->send($phone, $message, $context);
    }
}

