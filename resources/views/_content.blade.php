@php
    $routePrefix = config('notification-control.route.name_prefix', 'admin.notification-control.');
    $grouped = collect($items)->groupBy('category');
@endphp

<style>
    .ncs-root {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #0f172a;
        line-height: 1.5;
    }
    .ncs-root *, .ncs-root *::before, .ncs-root *::after {
        box-sizing: border-box;
    }
    .ncs-hidden { display: none !important; }
    .ncs-alert {
        padding: 0.85rem 1rem;
        border-radius: 0.625rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        border: 1px solid transparent;
    }
    .ncs-alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
    .ncs-alert-danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .ncs-alert ul { margin: 0; padding-left: 1.25rem; }

    .ncs-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .ncs-layout {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) minmax(0, 2.2fr);
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 992px) {
        .ncs-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ncs-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 576px) {
        .ncs-stats-grid { grid-template-columns: 1fr; }
    }

    .ncs-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.25rem;
    }
    .ncs-card:last-child { margin-bottom: 0; }
    .ncs-card-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.9rem 1.15rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .ncs-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .ncs-card-subtitle { font-size: 0.78rem; color: #64748b; margin-top: 0.15rem; }
    .ncs-card-body { padding: 1.15rem; }
    .ncs-card-body-flush { padding: 0; }

    .ncs-stat-label { font-size: 0.8rem; color: #64748b; margin-bottom: 0.25rem; }
    .ncs-stat-value { font-size: 1.45rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
    .ncs-stat-value-sm { font-size: 1rem; font-weight: 600; color: #0f172a; word-break: break-all; }
    .ncs-stat-sub { font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem; }

    .ncs-group-label {
        padding: 0.5rem 0.65rem 0.25rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    .ncs-nav-list { display: flex; flex-direction: column; gap: 0.25rem; padding: 0.5rem; }
    .ncs-nav-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.65rem 0.75rem;
        border-radius: 0.5rem;
        text-decoration: none;
        color: #1e293b;
        transition: background-color 0.15s ease;
    }
    .ncs-nav-item:hover { background: #f8fafc; color: #0f172a; }
    .ncs-nav-item.ncs-active { background: #2563eb; color: #ffffff; }
    .ncs-nav-item-title { font-size: 0.85rem; font-weight: 600; }
    .ncs-nav-item-type { font-size: 0.72rem; opacity: 0.78; }

    .ncs-badges { 
        display: flex;
        justify-content: right;
        flex-wrap: wrap; gap: 0.25rem; 
    }
    .ncs-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        background: #f1f5f9;
        color: #334155;
    }
    .ncs-nav-item.ncs-active .ncs-badge {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }
    .ncs-badge-primary { background: #eff6ff; color: #1d4ed8; }
    .ncs-badge-info { background: #ecfeff; color: #0e7490; }

    .ncs-meta-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 1rem;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 640px) { .ncs-meta-grid { grid-template-columns: 1fr; } }
    .ncs-meta-label { font-size: 0.78rem; color: #64748b; margin-bottom: 0.25rem; }
    .ncs-code {
        background: #f1f5f9;
        color: #0f172a;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.8rem;
        word-break: break-all;
    }
    .ncs-divider { border: 0; border-top: 1px solid #e2e8f0; margin: 1rem 0; }

    .ncs-policy-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
    }
    .ncs-switch-group { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; }
    .ncs-switch-label {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.85rem;
        cursor: pointer;
        user-select: none;
    }
    .ncs-switch-input {
        width: 1rem;
        height: 1rem;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .ncs-btn-group { display: inline-flex; border: 1px solid #cbd5e1; border-radius: 0.5rem; overflow: hidden; }
    .ncs-btn-toggle {
        background: #ffffff;
        border: 0;
        padding: 0.4rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
    }
    .ncs-btn-toggle + .ncs-btn-toggle { border-left: 1px solid #cbd5e1; }
    .ncs-btn-toggle.ncs-active { background: #0f172a; color: #ffffff; }

    .ncs-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.52rem 0.95rem;
        border-radius: 0.5rem;
        border: 1px solid transparent;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: opacity 0.15s ease;
    }
    .ncs-btn:hover { opacity: 0.92; }
    .ncs-btn-primary { background: #2563eb; color: #ffffff; }
    .ncs-btn-success { background: #16a34a; color: #ffffff; }
    .ncs-btn-outline { background: #ffffff; border-color: #cbd5e1; color: #334155; }

    .ncs-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 1rem;
    }
    .ncs-tab-btn {
        background: transparent;
        border: 0;
        border-bottom: 2px solid transparent;
        padding: 0.6rem 0.95rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
    }
    .ncs-tab-btn.ncs-active {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }

    .ncs-iframe-wrap {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        padding: 1rem;
        display: flex;
        justify-content: center;
    }
    .ncs-iframe {
        width: 100%;
        height: 520px;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background: #ffffff;
        transition: width 0.2s ease;
    }
    .ncs-iframe.ncs-mobile { width: 390px; max-width: 100%; }

    .ncs-wa-canvas {
        background: #efeae2;
        border-radius: 0.75rem;
        padding: 1.25rem;
        border: 1px solid #d6d3d1;
    }
    .ncs-wa-bubble {
        background: #ffffff;
        border-radius: 0 0.75rem 0.75rem 0.75rem;
        padding: 0.85rem 1rem;
        max-width: 480px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        white-space: pre-line;
        font-size: 0.9rem;
        color: #1e293b;
    }
    .ncs-inapp-card {
        border: 1px solid #e2e8f0;
        border-left: 4px solid #2563eb;
        border-radius: 0.65rem;
        padding: 1rem;
        background: #ffffff;
        max-width: 520px;
        margin-bottom: 1rem;
    }
    .ncs-pre {
        background: #0f172a;
        color: #f8fafc;
        padding: 0.9rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.8rem;
        overflow-x: auto;
        margin: 0;
    }

    .ncs-test-form {
        display: grid;
        grid-template-columns: 1.1fr 1.6fr 0.8fr 1fr;
        gap: 0.75rem;
        align-items: end;
    }
    @media (max-width: 768px) { .ncs-test-form { grid-template-columns: 1fr; } }
    .ncs-field-label { display: block; font-size: 0.8rem; font-weight: 600; color: #334155; margin-bottom: 0.3rem; }
    .ncs-input, .ncs-select {
        width: 100%;
        padding: 0.52rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #ffffff;
        color: #0f172a;
    }
    .ncs-input:focus, .ncs-select:focus {
        outline: 2px solid rgba(37, 99, 235, 0.2);
        border-color: #2563eb;
    }

    .ncs-table-wrap { overflow-x: auto; }
    .ncs-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .ncs-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        text-align: left;
        padding: 0.65rem 0.9rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .ncs-table td {
        padding: 0.65rem 0.9rem;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .ncs-empty { padding: 1.5rem; text-align: center; color: #64748b; font-size: 0.875rem; }
</style>

<div class="ncs-root">
    @if (session('status'))
        <div class="ncs-alert ncs-alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="ncs-alert ncs-alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ncs-stats-grid">
        <div class="ncs-card" style="margin-bottom: 0;">
            <div class="ncs-card-body">
                <div class="ncs-stat-label">Total Notifikasi</div>
                <div class="ncs-stat-value">{{ count($items) }}</div>
                <div class="ncs-stat-sub">Terdaftar &amp; Auto-Discovered</div>
            </div>
        </div>
        <div class="ncs-card" style="margin-bottom: 0;">
            <div class="ncs-card-body">
                <div class="ncs-stat-label">Mode Dispatch</div>
                <div class="ncs-stat-value" style="text-transform: uppercase;">{{ $dispatchMode }}</div>
                <div class="ncs-stat-sub">MAIL_DISPATCH_MODE</div>
            </div>
        </div>
        <div class="ncs-card" style="margin-bottom: 0;">
            <div class="ncs-card-body">
                <div class="ncs-stat-label">Sandbox Redirect</div>
                <div class="ncs-stat-value-sm">{{ $sandboxEmail ?: 'Nonaktif' }}</div>
                <div class="ncs-stat-sub">{{ app()->isProduction() ? 'Production Mode' : 'Non-Production Guard' }}</div>
            </div>
        </div>
        <div class="ncs-card" style="margin-bottom: 0;">
            <div class="ncs-card-body">
                <div class="ncs-stat-label">Aktivitas Tercatat</div>
                <div class="ncs-stat-value">{{ count($recentActivity) }}</div>
                <div class="ncs-stat-sub">Pengiriman terbaru</div>
            </div>
        </div>
    </div>

    <div class="ncs-layout">
        <div>
            <div class="ncs-card">
                <div class="ncs-card-header">
                    <h6 class="ncs-card-title">Katalog Notifikasi</h6>
                </div>
                <div class="ncs-card-body-flush">
                    @foreach ($grouped as $category => $groupItems)
                        <div class="ncs-group-label">{{ $category }}</div>
                        <div class="ncs-nav-list">
                            @foreach ($groupItems as $item)
                                @php
                                    $isSelected = $selected && $selected['key'] === $item['key'];
                                @endphp
                                <a
                                    href="{{ route($routePrefix . 'index', ['key' => $item['key']]) }}"
                                    class="ncs-nav-item {{ $isSelected ? 'ncs-active' : '' }}"
                                >
                                    <div>
                                        <div class="ncs-nav-item-title">{{ $item['title'] }}</div>
                                        <div class="ncs-nav-item-type">{{ $item['type'] }}</div>
                                    </div>
                                    <div class="ncs-badges">
                                        @foreach ($item['policies'] as $ch => $enabled)
                                            @if ($enabled && isset($channels[$ch]))
                                                <span class="ncs-badge">{{ $channels[$ch]['label'] ?? $ch }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div>
            @if ($selected)
                <div class="ncs-card">
                    <div class="ncs-card-header">
                        <div>
                            <h6 class="ncs-card-title">{{ $selected['title'] }}</h6>
                            <div class="ncs-card-subtitle">{{ $selected['class'] ?? $selected['key'] }}</div>
                        </div>
                        <span class="ncs-badge ncs-badge-primary">{{ $selected['category'] }}</span>
                    </div>
                    <div class="ncs-card-body">
                        <div class="ncs-meta-grid">
                            <div>
                                <div class="ncs-meta-label">Kondisi Pemicu</div>
                                <div style="font-weight: 500;">{{ $selected['trigger'] }}</div>
                            </div>
                            <div>
                                <div class="ncs-meta-label">Lokasi Trigger di Kode</div>
                                <span class="ncs-code">{{ $selected['called_in'] }}</span>
                            </div>
                        </div>

                        <hr class="ncs-divider">

                        <form action="{{ route($routePrefix . 'policies.update', ['key' => $selected['key']]) }}" method="POST" class="ncs-policy-form">
                            @csrf
                            <div class="ncs-switch-group">
                                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Channel Aktif:</span>
                                @foreach ($channels as $chKey => $chMeta)
                                    @php
                                        $isSupported = in_array($chKey, $selected['supported_channels'] ?? [], true);
                                    @endphp
                                    <label class="ncs-switch-label" for="ncs-ch-{{ $chKey }}" style="{{ $isSupported ? '' : 'opacity: 0.55; cursor: not-allowed;' }}">
                                        <input
                                            class="ncs-switch-input"
                                            type="checkbox"
                                            name="channels[{{ $chKey }}]"
                                            value="1"
                                            id="ncs-ch-{{ $chKey }}"
                                            {{ $isSupported && ! empty($selected['policies'][$chKey]) ? 'checked' : '' }}
                                            {{ $isSupported ? '' : 'disabled' }}
                                        >
                                        <span>
                                            {{ $chMeta['label'] ?? $chKey }}
                                            @if (! $isSupported)
                                                <small style="color: #94a3b8;">(belum ada di class)</small>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit" class="ncs-btn ncs-btn-primary">Simpan Kebijakan Channel</button>
                        </form>
                    </div>
                </div>

                <div class="ncs-card">
                    <div class="ncs-card-header">
                        <h6 class="ncs-card-title">Multi-Channel Preview</h6>
                        <div class="ncs-btn-group">
                            <button type="button" class="ncs-btn-toggle ncs-active" id="ncs-btn-desktop">Desktop</button>
                            <button type="button" class="ncs-btn-toggle" id="ncs-btn-mobile">Mobile</button>
                        </div>
                    </div>
                    <div class="ncs-card-body">
                        <div class="ncs-tabs" role="tablist">
                            <button class="ncs-tab-btn ncs-active" data-ncs-tab="ncs-pane-email" type="button">Email HTML</button>
                            <button class="ncs-tab-btn" data-ncs-tab="ncs-pane-database" type="button">In-App (Database)</button>
                            <button class="ncs-tab-btn" data-ncs-tab="ncs-pane-whatsapp" type="button">WhatsApp Text</button>
                        </div>

                        <div id="ncs-pane-email" class="ncs-tab-pane">
                            <div style="margin-bottom: 0.65rem; font-size: 0.85rem;">
                                <span style="color: #64748b;">Subject:</span>
                                <strong>{{ $emailPreview['subject'] ?? '-' }}</strong>
                            </div>
                            <div class="ncs-iframe-wrap">
                                <iframe
                                    id="ncs-email-iframe"
                                    class="ncs-iframe"
                                    src="{{ route($routePrefix . 'preview-email', ['key' => $selected['key']]) }}"
                                    title="Email Preview"
                                ></iframe>
                            </div>
                        </div>

                        <div id="ncs-pane-database" class="ncs-tab-pane ncs-hidden">
                            <div class="ncs-inapp-card">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $databasePreview['title'] ?? $selected['title'] }}</div>
                                <div style="font-size: 0.85rem; color: #64748b;">{{ $databasePreview['message'] ?? $selected['trigger'] }}</div>
                            </div>
                            <div class="ncs-meta-label">Payload JSON:</div>
                            <pre class="ncs-pre">{{ json_encode($databasePreview, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>

                        <div id="ncs-pane-whatsapp" class="ncs-tab-pane ncs-hidden">
                            <div class="ncs-wa-canvas">
                                <div class="ncs-wa-bubble">{{ $whatsappPreview }}</div>
                            </div>
                        </div>

                        <hr class="ncs-divider" style="margin: 1.25rem 0;">

                        <form action="{{ route($routePrefix . 'send-test', ['key' => $selected['key']]) }}" method="POST" class="ncs-test-form">
                            @csrf
                            <div>
                                <label class="ncs-field-label">Channel</label>
                                <select name="channel" class="ncs-select">
                                    <option value="mail">Email</option>
                                    <option value="whatsapp">WhatsApp (Driver)</option>
                                </select>
                            </div>
                            <div>
                                <label class="ncs-field-label">Tujuan (Email / No. WA)</label>
                                <input type="text" name="recipient" class="ncs-input" value="{{ auth()->user()?->email }}" placeholder="email@domain.com">
                            </div>
                            <div>
                                <label class="ncs-field-label">Mode</label>
                                <select name="mode" class="ncs-select">
                                    <option value="sync">Sync</option>
                                    <option value="async">Async</option>
                                    <option value="now">Now</option>
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="ncs-btn ncs-btn-success" style="width: 100%;">Kirim Test</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="ncs-card">
                <div class="ncs-card-header">
                    <h6 class="ncs-card-title">Riwayat Aktivitas Terbaru</h6>
                    @if (count($recentActivity) > 0)
                        <form action="{{ route($routePrefix . 'activity.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="ncs-btn ncs-btn-outline" style="padding: 0.35rem 0.7rem; font-size: 0.8rem;">Bersihkan</button>
                        </form>
                    @endif
                </div>
                <div class="ncs-card-body-flush">
                    @if (empty($recentActivity))
                        <div class="ncs-empty">Belum ada aktivitas pengiriman notifikasi yang tercatat di cache.</div>
                    @else
                        <div class="ncs-table-wrap">
                            <table class="ncs-table">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Channel</th>
                                        <th>Notifikasi</th>
                                        <th>Penerima</th>
                                        <th>Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentActivity as $log)
                                        <tr>
                                            <td style="color: #64748b;">{{ $log['sent_at'] ?? '-' }}</td>
                                            <td><span class="ncs-badge ncs-badge-info">{{ $log['channel'] ?? '-' }}</span></td>
                                            <td>{{ $log['name'] ?? '-' }}</td>
                                            <td>{{ $log['recipient'] ?? '-' }}</td>
                                            <td><span class="ncs-code">{{ $log['mode'] ?? '-' }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const iframe = document.getElementById('ncs-email-iframe');
        const btnDesktop = document.getElementById('ncs-btn-desktop');
        const btnMobile = document.getElementById('ncs-btn-mobile');

        if (iframe && btnDesktop && btnMobile) {
            btnDesktop.addEventListener('click', function () {
                iframe.classList.remove('ncs-mobile');
                btnDesktop.classList.add('ncs-active');
                btnMobile.classList.remove('ncs-active');
            });

            btnMobile.addEventListener('click', function () {
                iframe.classList.add('ncs-mobile');
                btnMobile.classList.add('ncs-active');
                btnDesktop.classList.remove('ncs-active');
            });
        }

        document.querySelectorAll('[data-ncs-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-ncs-tab');
                document.querySelectorAll('[data-ncs-tab]').forEach(b => b.classList.remove('ncs-active'));
                document.querySelectorAll('.ncs-tab-pane').forEach(p => p.classList.add('ncs-hidden'));
                this.classList.add('ncs-active');
                const pane = document.getElementById(targetId);
                if (pane) pane.classList.remove('ncs-hidden');
            });
        });
    });
</script>
