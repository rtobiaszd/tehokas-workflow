<?php

use App\Http\Controllers\WebhookController;
use App\Http\Middleware\SetTenantContext;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/workflows', [WebhookController::class, 'handle'])
    ->middleware([
        'resolve.tenant',
        'webhook.token',
    ])
    ->withoutMiddleware([
        SetTenantContext::class,
    ]);
