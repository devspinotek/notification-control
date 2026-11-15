<?php

use Devspinotek\NotificationControl\Http\Controllers\NotificationControlController;
use Illuminate\Support\Facades\Route;

$routeConfig = config('notification-control.route', []);

Route::prefix($routeConfig['prefix'] ?? 'backend/notification-control')
    ->as($routeConfig['name_prefix'] ?? 'admin.notification-control.')
    ->middleware($routeConfig['middleware'] ?? ['web', 'auth'])
    ->group(function () {
        Route::get('/', [NotificationControlController::class, 'index'])->name('index');
        Route::get('/{key}/preview-email', [NotificationControlController::class, 'previewEmail'])->name('preview-email');
        Route::post('/{key}/policies', [NotificationControlController::class, 'updatePolicies'])->name('policies.update');
        Route::post('/{key}/send-test', [NotificationControlController::class, 'sendTest'])->name('send-test');
        Route::post('/activity/clear', [NotificationControlController::class, 'clearActivity'])->name('activity.clear');
    });

