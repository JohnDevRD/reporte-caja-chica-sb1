<?php

declare(strict_types=1);

function buildApiUrl(array $overrides = []): string
{
    return API_BASE . '?' . buildQueryString($overrides);
}

function callApi(string $url): ?array
{
    $ch = curl_init();
    if ($ch === false) {
        return ['ok' => false, 'error' => 'No se pudo inicializar cURL'];
    }

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => API_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => API_CONNECT_TIMEOUT,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => API_SSL_VERIFY,
        CURLOPT_FOLLOWLOCATION => true,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        return ['ok' => false, 'error' => "Error de conexión: {$error}"];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        return ['ok' => false, 'error' => "Error HTTP {$httpCode}"];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['ok' => false, 'error' => 'Respuesta JSON inválida'];
    }

    return $data;
}

function fetchAllApiPages(array $overrides = []): array
{
    $page = max(1, (int) ($overrides['page'] ?? 1));
    $limit = max(1, (int) ($overrides['limit'] ?? MAX_LIMIT));
    $records = [];
    $pagesTotal = 1;
    $lastResponse = null;

    do {
        $data = callApi(buildApiUrl(array_merge($overrides, [
            'page'  => (string) $page,
            'limit' => (string) $limit,
        ])));

        if ($data === null) {
            return [
                'ok' => false,
                'error' => 'No se pudo conectar con el servidor',
                'data' => $records,
            ];
        }

        if (isset($data['ok']) && $data['ok'] === false) {
            return [
                'ok' => false,
                'error' => $data['error'] ?? 'Error desconocido',
                'data' => $records,
            ];
        }

        if (!isset($data['ok']) || $data['ok'] !== true) {
            return [
                'ok' => false,
                'error' => 'Formato de respuesta inesperado',
                'data' => $records,
            ];
        }

        $records = array_merge($records, $data['data'] ?? []);
        $pagesTotal = max(1, (int) ($data['pages_total'] ?? 1));
        $lastResponse = $data;
        $page++;
    } while ($page <= $pagesTotal);

    return [
        'ok' => true,
        'data' => $records,
        'total' => (int) ($lastResponse['total'] ?? count($records)),
        'pages_total' => $pagesTotal,
        'records_count' => count($records),
        'last_response' => $lastResponse ?? [],
    ];
}
