<?php

declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

loadEnv(__DIR__ . '/../.env');

function env(string $key, string $default = ''): string
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

define('API_BASE',          env('API_BASE', 'http://localhost:8000/api-reportes-sb1/api/v1/consultas/caja_chica/reporte.php'));
define('API_TIMEOUT',       (int) env('API_TIMEOUT', '30'));
define('API_CONNECT_TIMEOUT', (int) env('API_CONNECT_TIMEOUT', '10'));
define('API_SSL_VERIFY',    filter_var(env('API_SSL_VERIFY', 'false'), FILTER_VALIDATE_BOOLEAN));

define('DEFAULT_LIMIT',     (int) env('DEFAULT_LIMIT', '100'));
define('MAX_LIMIT',         (int) env('MAX_LIMIT', '500'));
define('MIN_LIMIT',         (int) env('MIN_LIMIT', '1'));

define('APP_NAME',          env('APP_NAME', 'Reporte de Caja Chica'));
define('APP_SUBTITLE',      env('APP_SUBTITLE', 'Consulta y seguimiento de movimientos'));
define('APP_DEBUG',         filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));
