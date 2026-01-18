<?php

namespace App\Integrations;

use App\Integrations\Contracts\IntegrationDriverInterface;
use App\Integrations\Email\EmailDriver;
use App\Integrations\GoogleSheets\GoogleSheetsDriver;
use App\Integrations\Slack\SlackDriver;
use App\Integrations\Teams\TeamsDriver;
use App\Integrations\Webhook\WebhookDriver;
use App\Integrations\WhatsApp\WhatsAppDriver;
use Illuminate\Contracts\Container\Container;

class IntegrationManager
{
    private array $drivers = [
        'send_email' => EmailDriver::class,
        'slack_message' => SlackDriver::class,
        'teams_message' => TeamsDriver::class,
        'whatsapp_message' => WhatsAppDriver::class,
        'http_request' => WebhookDriver::class,
        'webhook' => WebhookDriver::class,
        'google_sheets_append' => GoogleSheetsDriver::class,
    ];

    public function __construct(private Container $container)
    {
    }

    public function forAction(string $actionType): ?IntegrationDriverInterface
    {
        $driverClass = $this->drivers[$actionType] ?? null;

        if (! $driverClass) {
            return null;
        }

        return $this->container->make($driverClass);
    }

    public function register(string $actionType, string $driverClass): void
    {
        $this->drivers[$actionType] = $driverClass;
    }
}
