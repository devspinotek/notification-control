<?php

namespace Devspinotek\NotificationControl\Support;

use Illuminate\Support\Facades\File;

class ChannelPolicyStore
{
    public function all(): array
    {
        $path = $this->storagePath();
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    public function getForNotification(string $key, array $defaultChannels = ['mail']): array
    {
        $policies = $this->all();
        if (! isset($policies[$key]) || ! is_array($policies[$key])) {
            $result = [];
            foreach (array_keys(config('notification-control.channels', ['mail' => []])) as $channel) {
                $result[$channel] = in_array($channel, $defaultChannels, true);
            }

            return $result;
        }

        return $policies[$key];
    }

    public function isChannelEnabled(string $key, string $channel, array $defaultChannels = ['mail']): bool
    {
        $policy = $this->getForNotification($key, $defaultChannels);

        if (array_key_exists($channel, $policy)) {
            return (bool) $policy[$channel];
        }

        return in_array($channel, $defaultChannels, true);
    }

    public function setForNotification(string $key, array $channels): void
    {
        $policies = $this->all();
        $normalized = [];

        foreach (array_keys(config('notification-control.channels', ['mail' => []])) as $channel) {
            $normalized[$channel] = (bool) ($channels[$channel] ?? false);
        }

        $policies[$key] = $normalized;

        $path = $this->storagePath();
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($policies, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function storagePath(): string
    {
        return (string) config('notification-control.policy_storage_path', storage_path('app/notification-control-policies.json'));
    }
}

