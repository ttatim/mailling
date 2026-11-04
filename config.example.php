<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Mailing',
        'url' => 'http://localhost:8000',
        'environment' => 'local',
        'encryption_key' => 'GERE_UMA_CHAVE_ALEATORIA_DE_32_BYTES_EM_BASE64',
        'default_hourly_limit' => 30,
    ],
    'database' => [
        'host' => '127.0.0.1', 'port' => 3306, 'database' => 'mailling',
        'username' => 'root', 'password' => '', 'charset' => 'utf8mb4',
    ],
    'security' => ['session_name' => 'mailling_session'],
    'billing' => ['gateway' => 'mercado_pago', 'access_token' => 'TEST-CHANGE-ME'],
];
