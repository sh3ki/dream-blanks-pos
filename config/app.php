<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Dream Blanks POS',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'url' => $_ENV['APP_URL'] ?? '',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    'session_name' => $_ENV['SESSION_NAME'] ?? 'dream_pos_session',
    'session_timeout' => (int) ($_ENV['SESSION_TIMEOUT'] ?? 30),
    'remember_days' => (int) ($_ENV['REMEMBER_DAYS'] ?? 14),
    'csrf_token_name' => $_ENV['CSRF_TOKEN_NAME'] ?? '_token',
];
