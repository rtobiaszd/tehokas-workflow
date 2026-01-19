<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/workflows', [WebhookController::class, 'handle'])
    ->middleware(['webhook.token']);
