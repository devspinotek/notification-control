<?php

namespace Devspinotek\NotificationControl\Http\Controllers;

use Devspinotek\NotificationControl\Support\ChannelPolicyStore;
use Devspinotek\NotificationControl\Support\NotificationActivityRecorder;
use Devspinotek\NotificationControl\Support\NotificationCatalog;
use Devspinotek\NotificationControl\Support\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationControlController extends Controller
{
    public function __construct(
        protected NotificationCatalog $catalog,
        protected ChannelPolicyStore $policyStore,
        protected NotificationActivityRecorder $activityRecorder
    ) {}

    public function index(Request $request)
    {
        $items = $this->catalog->all();
        $selectedKey = $request->query('key');

        if (! $selectedKey || ! isset($items[$selectedKey])) {
            $selectedKey = array_key_first($items);
        }

        $selected = $selectedKey ? ($items[$selectedKey] ?? null) : null;
        $emailPreview = $selected ? $this->catalog->renderEmail($selected['key']) : null;
        $databasePreview = $selected ? $this->catalog->renderDatabasePreview($selected['key']) : [];
        $whatsappPreview = $selected ? $this->catalog->renderWhatsAppPreview($selected['key']) : '';
        $recentActivity = $this->activityRecorder->recent();

        $channels = config('notification-control.channels', []);
        $dispatchMode = NotificationDispatcher::mode();
        $sandboxEmail = config('notification-control.dispatch.sandbox.redirect_email_to');
        $hostLayout = config('notification-control.layout');

        return view('notification-control::index', compact(
            'items',
            'selected',
            'emailPreview',
            'databasePreview',
            'whatsappPreview',
            'recentActivity',
            'channels',
            'dispatchMode',
            'sandboxEmail',
            'hostLayout'
        ));
    }

    public function previewEmail(string $key)
    {
        $item = $this->catalog->find($key);
        if (! $item) {
            abort(404);
        }

        $rendered = $this->catalog->renderEmail($key);

        return response($rendered['html'], 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function updatePolicies(Request $request, string $key)
    {
        $item = $this->catalog->find($key);
        if (! $item) {
            abort(404);
        }

        $channelsInput = $request->input('channels', []);
        $this->policyStore->setForNotification($key, is_array($channelsInput) ? $channelsInput : []);

        if (function_exists('notyf')) {
            notyf('Kebijakan channel notifikasi berhasil diperbarui.');
        }

        return redirect()->route(config('notification-control.route.name_prefix', 'admin.notification-control.') . 'index', ['key' => $key]);
    }

    public function sendTest(Request $request, string $key)
    {
        $item = $this->catalog->find($key);
        if (! $item) {
            abort(404);
        }

        $channel = $request->input('channel', 'mail');
        $rules = [
            'channel' => 'required|in:mail,whatsapp',
            'mode' => 'nullable|in:async,sync,now',
            'recipient' => $channel === 'mail' ? 'required|email' : 'required|string|max:32',
        ];

        $validated = $request->validate($rules);

        $sent = $this->catalog->sendTest(
            $key,
            $validated['recipient'],
            $validated['channel'],
            $validated['mode'] ?? null
        );

        if (function_exists('notyf')) {
            if ($sent) {
                notyf('Notifikasi uji coba berhasil diproses.');
            } else {
                notyf()->addError('Sample notifikasi belum tersedia untuk dikirim.');
            }
        }

        return redirect()->route(config('notification-control.route.name_prefix', 'admin.notification-control.') . 'index', ['key' => $key])
            ->with($sent ? 'status' : 'error', $sent ? 'Notifikasi uji coba berhasil diproses.' : 'Sample notifikasi belum tersedia.');
    }

    public function clearActivity()
    {
        $this->activityRecorder->clear();

        if (function_exists('notyf')) {
            notyf('Riwayat aktivitas notifikasi dibersihkan.');
        }

        return back();
    }
}

