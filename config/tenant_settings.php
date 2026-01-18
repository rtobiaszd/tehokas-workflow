<?php

return [
    'defaults' => [
        'notifications' => [
            'email' => [
                'enabled' => true,
            ],
            'slack' => [
                'enabled' => false,
            ],
            'whatsapp' => [
                'enabled' => false,
            ],
        ],
        'integrations' => [
            'email' => [
                'enabled' => true,
                'credentials' => [
                    'host' => '',
                    'port' => '',
                    'encryption' => 'tls',
                    'username' => '',
                    'password' => '',
                    'from_name' => '',
                    'from_email' => '',
                ],
            ],
            'webhook' => [
                'enabled' => true,
                'token' => '',
                'credentials' => [
                    'headers' => [],
                    'timeout' => 10,
                    'retries' => 0,
                ],
            ],
            'slack' => [
                'enabled' => false,
                'credentials' => [
                    'bot_token' => '',
                    'default_channel' => '',
                    'webhook_url' => '',
                ],
            ],
            'whatsapp' => [
                'enabled' => false,
                'credentials' => [
                    'provider' => '',
                    'api_token' => '',
                    'phone_number_id' => '',
                ],
            ],
            'teams' => [
                'enabled' => false,
                'credentials' => [
                    'webhook_url' => '',
                    'tenant_id' => '',
                ],
            ],
            'google_sheets' => [
                'enabled' => false,
                'credentials' => [
                    'service_account_json' => '',
                    'spreadsheet_id' => '',
                    'default_sheet' => '',
                ],
            ],
            'salesforce' => [
                'enabled' => false,
                'credentials' => [
                    'client_id' => '',
                    'client_secret' => '',
                    'refresh_token' => '',
                    'instance_url' => '',
                ],
            ],
            'hubspot' => [
                'enabled' => false,
                'credentials' => [
                    'access_token' => '',
                    'refresh_token' => '',
                ],
            ],
        ],
        'policies' => [
            'workflow' => [
                'rate_limit' => 120,
                'retry_on_fail' => true,
            ],
        ],
        'observability' => [
            'logs' => [
                'enabled' => true,
            ],
            'external' => [
                'enabled' => false,
            ],
        ],
    ],
];
