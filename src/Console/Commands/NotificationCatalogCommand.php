<?php

namespace Devspinotek\NotificationControl\Console\Commands;

use Devspinotek\NotificationControl\Support\NotificationCatalog;
use Illuminate\Console\Command;

class NotificationCatalogCommand extends Command
{
    protected $signature = 'notification:catalog';

    protected $description = 'List all registered and auto-discovered notifications with their active channels and triggers';

    public function handle(NotificationCatalog $catalog): int
    {
        $items = $catalog->all();

        if (empty($items)) {
            $this->warn('Tidak ada notifikasi yang ditemukan.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($items as $item) {
            $activeChannels = collect($item['policies'])
                ->filter()
                ->keys()
                ->implode(', ');

            $rows[] = [
                $item['key'],
                $item['category'],
                $item['title'],
                $activeChannels ?: '-',
                $item['trigger'],
            ];
        }

        $this->table(['Key', 'Category', 'Title', 'Active Channels', 'Trigger'], $rows);

        return self::SUCCESS;
    }
}

