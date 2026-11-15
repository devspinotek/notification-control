@if ($hostLayout === 'backend-layout')
    <x-backend-layout>
        <x-page-header
            title="Notification Control System"
            subtitle="Kelola kebijakan channel, dokumentasi pemicu, multi-channel preview, dan uji kirim notifikasi."
        />

        @include('notification-control::_content')
    </x-backend-layout>
@else
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notification Control System</title>
    </head>
    <body style="margin: 0; background: #f8fafc; font-family: system-ui, -apple-system, sans-serif;">
        <div style="max-width: 1240px; margin: 0 auto; padding: 1.5rem;">
            <div style="margin-bottom: 1.5rem;">
                <h2 style="margin: 0 0 0.25rem; font-size: 1.5rem; font-weight: 700; color: #0f172a;">Notification Control System</h2>
                <p style="margin: 0; font-size: 0.9rem; color: #64748b;">Kelola kebijakan channel, dokumentasi pemicu, multi-channel preview, dan uji kirim notifikasi.</p>
            </div>
            @include('notification-control::_content')
        </div>
    </body>
    </html>
@endif
