<?php

namespace App\Integrations\Email;

use App\Integrations\Contracts\IntegrationDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Services\TenantSettingService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailDriver implements IntegrationDriverInterface
{
    public function __construct(
        private TenantSettingService $tenantSettings,
        private LoggerInterface $logger
    ) {
    }

    public function send(array $payload): void
    {
        // 1️⃣ Verifica se a integração está habilitada
        if (! $this->tenantSettings->integrationEnabled('email', true)) {
            throw new IntegrationNotConfiguredException(
                'Email integration is disabled for this tenant.'
            );
        }

        // 2️⃣ Carrega credenciais do tenant
        $credentials = $this->tenantSettings->integrationValue('email', 'credentials', []);

        $required = [
            'host',
            'port',
            'username',
            'password',
            'from_name',
            'from_email',
        ];

        foreach ($required as $field) {
            if (empty($credentials[$field])) {
                throw new IntegrationNotConfiguredException(
                    "Email credential '{$field}' is missing."
                );
            }
        }

        // 3️⃣ Valida payload
        $to = $payload['to'] ?? null;
        if (! $to) {
            throw new IntegrationException('Email payload must include a recipient.');
        }

        $recipients = is_array($to) ? $to : [$to];
        $subject = (string) ($payload['subject'] ?? 'Workflow notification');
        $body = (string) ($payload['body'] ?? json_encode($payload, JSON_PRETTY_PRINT));

        $cc = $payload['cc'] ?? [];
        $bcc = $payload['bcc'] ?? [];

        // 4️⃣ Cria mailer SMTP dinâmico (POR TENANT)
        $mailerName = 'tenant_smtp_' . $this->tenantSettings->tenantId();

        Config::set("mail.mailers.$mailerName", [
            'transport' => 'smtp',
            'host' => $credentials['host'],
            'port' => $credentials['port'],
            'encryption' => $credentials['encryption'] ?? 'tls',
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'timeout' => 15,
            'local_domain' => request()->getHost(),
        ]);

        // 5️⃣ Envia o e-mail usando o mailer correto
        Mail::mailer($mailerName)->raw($body, function ($message) use (
            $recipients,
            $subject,
            $credentials,
            $cc,
            $bcc
        ) {
            $message
                ->to($recipients)
                ->subject($subject)
                ->from(
                    $credentials['from_email'],
                    $credentials['from_name']
                );

            if (! empty($cc)) {
                $message->cc($cc);
            }

            if (! empty($bcc)) {
                $message->bcc($bcc);
            }
        });

        // 6️⃣ Loga sucesso REAL (SMTP foi usado)
        $this->logger->info('integration_email_sent', [
            'mailer' => $mailerName,
            'recipients' => $recipients,
            'subject' => $subject,
            'host' => $credentials['host'],
            'port' => $credentials['port'],
        ]);
    }
}
