<?php

use App\Http\Controllers\WebhookController;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\ValidateWebhookToken;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/workflows', [WebhookController::class, 'handle'])
    ->middleware([
        ValidateWebhookToken::class,
        IdentifyTenant::class,
    ]);
