<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantSwitchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookSettingsController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::post('/tenants/switch', TenantSwitchController::class)->name('tenants.switch');

    Route::prefix('workflows')->name('workflows.')->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::get('/create', [WorkflowController::class, 'create'])->name('create');
        Route::get('/{workflow}/edit', [WorkflowController::class, 'edit'])->name('edit');
        Route::post('/', [WorkflowController::class, 'store'])->name('store');
        Route::put('/{workflow}', [WorkflowController::class, 'update'])->name('update');
        Route::patch('/{workflow}/toggle', [WorkflowController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('index');
        Route::get('/create', [TenantController::class, 'create'])->name('create');
        Route::post('/', [TenantController::class, 'store'])->name('store');
        Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
        Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
    });

    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/feed', [LogController::class, 'feed'])->name('logs.feed');
    Route::get('/webhooks', [WebhookSettingsController::class, 'index'])->name('webhooks.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/webhook-token', [SettingsController::class, 'regenerateWebhookToken'])->name('settings.webhook-token');
    Route::post('/settings/integrations/test', [SettingsController::class, 'testIntegration'])->name('settings.integrations.test');
});
