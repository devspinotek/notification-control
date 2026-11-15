<?php

namespace Devspinotek\NotificationControl\Support;

use Devspinotek\NotificationControl\Attributes\NotificationMeta;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;

class NotificationCatalog
{
    public function __construct(protected ChannelPolicyStore $policyStore) {}

    public function all(): array
    {
        $items = [];
        $registeredClasses = [];

        foreach (config('notification-control.catalog', []) as $key => $entry) {
            $class = $entry['class'] ?? null;
            if ($class) {
                $registeredClasses[] = $class;
            }

            $sample = $entry['sample'] ?? null;
            $instance = $this->buildSampleInstance(['class' => $class, 'sample' => $sample]);
            $defaultChannels = $this->detectChannels($instance, $class, $entry['channels'] ?? ['mail']);

            $items[$key] = [
                'key' => $key,
                'class' => $class,
                'type' => $this->resolveType($class),
                'title' => $entry['title'] ?? ($class ? class_basename($class) : $key),
                'category' => $entry['category'] ?? 'Umum',
                'trigger' => $entry['trigger'] ?? '-',
                'called_in' => $entry['called_in'] ?? '-',
                'default_channels' => $defaultChannels,
                'supported_channels' => $defaultChannels,
                'policies' => $this->policyStore->getForNotification($key, $defaultChannels),
                'sample' => $sample,
                'database_preview' => $entry['database_preview'] ?? null,
                'whatsapp_preview' => $entry['whatsapp_preview'] ?? null,
                'discovered' => false,
            ];
        }

        foreach ($this->discoverClasses() as $class) {
            if (in_array($class, $registeredClasses, true)) {
                continue;
            }

            $meta = $this->readAttributeMeta($class);
            $key = Str::kebab(class_basename($class));
            $instance = $this->buildSampleInstance(['class' => $class, 'sample' => null]);
            $defaultChannels = $this->detectChannels($instance, $class, $meta?->channels ?? ['mail']);

            $items[$key] = [
                'key' => $key,
                'class' => $class,
                'type' => $this->resolveType($class),
                'title' => $meta?->title ?: class_basename($class),
                'category' => $meta?->category ?: (Str::contains($class, '\\Mail\\') ? 'Mail (Auto-Discovered)' : 'Notification (Auto-Discovered)'),
                'trigger' => $meta?->trigger ?: 'Belum didokumentasikan di config/notification-control.php atau #[NotificationMeta].',
                'called_in' => $meta?->calledIn ?: '-',
                'default_channels' => $defaultChannels,
                'supported_channels' => $defaultChannels,
                'policies' => $this->policyStore->getForNotification($key, $defaultChannels),
                'sample' => null,
                'database_preview' => null,
                'whatsapp_preview' => null,
                'discovered' => true,
            ];
        }

        return $items;
    }

    public function find(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }

    public function renderEmail(string $key): array
    {
        $item = $this->find($key);
        if (! $item) {
            return ['subject' => '-', 'html' => '<p>Notifikasi tidak ditemukan.</p>'];
        }

        $instance = $this->buildSampleInstance($item);
        if (! $instance) {
            return [
                'subject' => $item['title'],
                'html' => '<div style="font-family:sans-serif;padding:24px;color:#475569;">Preview otomatis belum tersedia untuk class <code>' . e($item['class'] ?? $key) . '</code>. Tambahkan <code>sample</code> builder di <code>config/notification-control.php</code>.</div>',
            ];
        }

        $previousResetUrlCallback = ResetPassword::$createUrlCallback;

        try {
            if ($instance instanceof Mailable) {
                $html = $instance->render();
                $subject = method_exists($instance, 'envelope')
                    ? ($instance->envelope()->subject ?? $item['title'])
                    : $item['title'];

                return ['subject' => $subject, 'html' => $html];
            }

            if ($instance instanceof Notification && method_exists($instance, 'toMail')) {
                if ($instance instanceof ResetPassword && ! Route::has('password.reset')) {
                    ResetPassword::createUrlUsing(fn ($notifiable, $token) => url('/password/reset/' . $token . '?preview=1'));
                }

                $notifiable = $this->makeDummyNotifiable();
                $mailMessage = $instance->toMail($notifiable);
                if ($mailMessage instanceof MailMessage) {
                    return [
                        'subject' => $mailMessage->subject ?: $item['title'],
                        'html' => (string) $mailMessage->render(),
                    ];
                }
            }
        } catch (\Throwable $e) {
            return [
                'subject' => $item['title'],
                'html' => '<div style="font-family:sans-serif;padding:24px;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;"><strong>Gagal merender preview:</strong> ' . e($e->getMessage()) . '</div>',
            ];
        } finally {
            ResetPassword::$createUrlCallback = $previousResetUrlCallback;
        }

        return ['subject' => $item['title'], 'html' => '<p>Channel email tidak tersedia pada class ini.</p>'];
    }

    public function renderDatabasePreview(string $key): array
    {
        $item = $this->find($key);
        if (! $item) {
            return [];
        }

        if (is_callable($item['database_preview'])) {
            return (array) call_user_func($item['database_preview']);
        }

        if (is_array($item['database_preview'])) {
            return $item['database_preview'];
        }

        $instance = $this->buildSampleInstance($item);
        if ($instance instanceof Notification && method_exists($instance, 'toArray')) {
            return (array) $instance->toArray($this->makeDummyNotifiable());
        }

        return [
            'title' => $item['title'],
            'message' => $item['trigger'],
        ];
    }

    public function renderWhatsAppPreview(string $key): string
    {
        $item = $this->find($key);
        if (! $item) {
            return '';
        }

        if (is_callable($item['whatsapp_preview'])) {
            return (string) call_user_func($item['whatsapp_preview']);
        }

        if (is_string($item['whatsapp_preview']) && $item['whatsapp_preview'] !== '') {
            return $item['whatsapp_preview'];
        }

        return "*{$item['title']}*\n\n{$item['trigger']}";
    }

    public function sendTest(string $key, string $recipient, string $channel = 'mail', ?string $mode = null): bool
    {
        $item = $this->find($key);
        if (! $item) {
            return false;
        }

        if ($channel === 'whatsapp') {
            $text = $this->renderWhatsAppPreview($key);

            return NotificationDispatcher::sendWhatsApp($recipient, $text, ['notification_key' => $key]);
        }

        $instance = $this->buildSampleInstance($item);
        if (! $instance) {
            return false;
        }

        if ($instance instanceof Mailable) {
            NotificationDispatcher::send($recipient, $instance, $mode);

            return true;
        }

        if ($instance instanceof Notification) {
            $notifiable = $this->makeDummyNotifiable($recipient);
            NotificationDispatcher::notify($notifiable, $instance, $mode);

            return true;
        }

        return false;
    }

    private function buildSampleInstance(array $item): mixed
    {
        if (is_callable($item['sample'] ?? null)) {
            return call_user_func($item['sample']);
        }

        $class = $item['class'] ?? null;
        if ($class && class_exists($class)) {
            $ref = new ReflectionClass($class);
            $ctor = $ref->getConstructor();
            if (! $ctor || $ctor->getNumberOfRequiredParameters() === 0) {
                return $ref->newInstance();
            }
        }

        return null;
    }

    private function makeDummyNotifiable(string $email = 'tester@example.com'): object
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        if (class_exists($userModel)) {
            $model = new $userModel([
                'name' => 'Tester Preview',
                'email' => $email,
                'phone' => '081234567890',
            ]);
            $model->id = 9999;

            return $model;
        }

        return new class($email) {
            use \Illuminate\Notifications\Notifiable;

            public int $id = 9999;
            public string $name = 'Tester Preview';
            public string $email;

            public function __construct(string $email)
            {
                $this->email = $email;
            }

            public function getKey(): int
            {
                return $this->id;
            }
        };
    }

    private function discoverClasses(): array
    {
        $classes = [];
        foreach (config('notification-control.discovery_paths', []) as $path) {
            if (! is_string($path) || ! File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relative = Str::after($file->getPathname(), app_path() . DIRECTORY_SEPARATOR);
                $class = 'App\\' . str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relative);

                if (class_exists($class)) {
                    $classes[] = $class;
                }
            }
        }

        return $classes;
    }

    private function readAttributeMeta(string $class): ?NotificationMeta
    {
        if (! class_exists($class)) {
            return null;
        }

        $attrs = (new ReflectionClass($class))->getAttributes(NotificationMeta::class);

        return $attrs ? $attrs[0]->newInstance() : null;
    }

    private function resolveType(?string $class): string
    {
        if (! $class || ! class_exists($class)) {
            return 'custom';
        }

        if (is_subclass_of($class, Mailable::class)) {
            return 'mailable';
        }

        if (is_subclass_of($class, Notification::class)) {
            return 'notification';
        }

        return 'custom';
    }

    private function detectChannels(mixed $instance, ?string $class, array $fallback = ['mail']): array
    {
        if ($instance instanceof Mailable || ($class && is_subclass_of($class, Mailable::class))) {
            return ['mail'];
        }

        if ($instance instanceof Notification && method_exists($instance, 'via')) {
            try {
                $channels = $instance->via($this->makeDummyNotifiable());
                if (is_array($channels) && ! empty($channels)) {
                    return array_values(array_unique(array_map('strval', $channels)));
                }
            } catch (\Throwable) {
                // Fallback ke konfigurasi jika via() membutuhkan state khusus
            }
        }

        return $fallback;
    }
}
