<?php

namespace App\Integrations\Email;

use App\Integrations\Contracts\IntegrationDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Services\TenantSettingService;
use Illuminate\Support\Facades\Mail;

class EmailDriver implements IntegrationDriverInterface
{
    public function __construct(
        private TenantSettingService $tenantSettings,
        private LoggerInterface $logger
    ) {
    }

    public function send(array $payload): void
    {
        if (! $this->tenantSettings->integrationEnabled('email', true)) {
            throw new IntegrationNotConfiguredException('Email integration is disabled for this tenant.');
        }

        $credentials = $this->tenantSettings->integrationValue('email', 'credentials', []);
        $hasCredentials = ! empty($credentials['host'])
            && ! empty($credentials['port'])
            && ! empty($credentials['username'])
            && ! empty($credentials['password'])
            && ! empty($credentials['from_name'])
            && ! empty($credentials['from_email']);

        if (! $hasCredentials) {
            throw new IntegrationNotConfiguredException('Email credentials are missing.');
        }

        $to = $payload['to'] ?? null;

        if (! $to) {
            throw new IntegrationException('Email payload must include a recipient.');
        }

        $recipients = is_array($to) ? $to : [$to];
        $subject = (string) ($payload['subject'] ?? 'Workflow notification');
        $body = (string) ($payload['body'] ?? json_encode($payload, JSON_PRETTY_PRINT));

        $fromAddress = $credentials['from_email'] ?? config('mail.from.address');
        $fromName = $credentials['from_name'] ?? config('mail.from.name');
        $cc = $payload['cc'] ?? [];
        $bcc = $payload['bcc'] ?? [];

        Mail::raw($body, function ($message) use ($recipients, $subject, $fromAddress, $fromName, $cc, $bcc) {
            $message->to($recipients)->subject($subject);

            if ($fromAddress) {
                $message->from($fromAddress, $fromName);
            }

            if ($cc) {
                $message->cc($cc);
            }

            if ($bcc) {
                $message->bcc($bcc);
            }
        });

        $this->logger->info('integration_email_sent', [
            'recipients' => $recipients,
            'subject' => $subject,
        ]);
    }
}
