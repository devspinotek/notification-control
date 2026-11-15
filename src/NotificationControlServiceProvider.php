<?php

namespace Devspinotek\NotificationControl;

use Devspinotek\NotificationControl\Console\Commands\NotificationCatalogCommand;
use Devspinotek\NotificationControl\Contracts\WhatsAppDriverInterface;
use Devspinotek\NotificationControl\Drivers\LogWhatsAppDriver;
use Devspinotek\NotificationControl\Support\ChannelPolicyStore;
use Devspinotek\NotificationControl\Support\NotificationActivityRecorder;
use Devspinotek\NotificationControl\Support\NotificationCatalog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class NotificationControlServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/notification-control.php', 'notification-control');

        $this->app->singleton(ChannelPolicyStore::class);
        $this->app->singleton(NotificationActivityRecorder::class);
        $this->app->singleton(NotificationCatalog::class);
        $this->app->bindIf(WhatsAppDriverInterface::class, LogWhatsAppDriver::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'notification-control');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                NotificationCatalogCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/notification-control.php' => config_path('notification-control.php'),
            ], 'notification-control-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/notification-control'),
            ], 'notification-control-views');
        }

        Event::listen(MessageSent::class, [NotificationActivityRecorder::class, 'recordMessageSent']);
        Event::listen(NotificationSent::class, [NotificationActivityRecorder::class, 'recordNotificationSent']);
    }
}

