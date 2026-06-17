<?php

declare(strict_types=1);

function getParam(string $key, ?string $default = null): ?string
{
    $value = $_GET[$key] ?? null;
    if ($value === null || $value === '') {
        return $default;
    }
    return trim($value);
}

function formatDate(?string $date): string
{
    if (!$date || $date === '0000-00-00') {
        return '-';
    }
    $parts = explode('-', $date);
    if (count($parts) === 3) {
        return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
    }
    return $date;
}

function formatCurrency(?string $amount): string
{
    if ($amount === null || $amount === '') {
        return '0.00';
    }
    return number_format((float) $amount, 2, '.', ',');
}

function html(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function buildQueryString(array $overrides = []): string
{
    $fields = [
        'desde', 'hasta',
        'ocr_code', 'descripcion', 'comments_factura',
        'page', 'limit'
    ];

    $params = [];
    foreach ($fields as $field) {
        $value = $overrides[$field] ?? getParam($field);
        if ($value !== null && $value !== '') {
            $params[$field] = $value;
        }
    }

    return http_build_query($params);
}

function normalizeLimit(?string $limit): int
{
    $val = (int) ($limit ?? (string) DEFAULT_LIMIT);
    if ($val < MIN_LIMIT || $val > MAX_LIMIT) {
        return DEFAULT_LIMIT;
    }
    return $val;
}

function getActiveFilters(): array
{
    $activeFilters = [];
    $filterLabels = [
        'desde'            => 'Desde',
        'hasta'            => 'Hasta',
        'ocr_code'         => 'Sucursal',
        'descripcion'      => 'Descripción',
        'comments_factura' => 'Comentarios Factura',
    ];

    foreach ($filterLabels as $key => $label) {
        $val = getParam($key);
        if ($val !== null && $val !== '') {
            $displayVal = $val;
            if (in_array($key, ['desde', 'hasta'], true)) {
                $displayVal = formatDate($val);
            }
            $activeFilters[] = ['key' => $key, 'label' => $label, 'value' => $displayVal];
        }
    }

    return $activeFilters;
}

/**
 * Filtra registros (array de filas) aplicando filtros de texto en forma
 * case-insensitive. Para cada campo de filtro textual presente en GET,
 * realiza una búsqueda con stripos (contiene) en el valor del registro.
 * Devuelve solo los registros que cumplen con todos los filtros textuales.
 * 
 * Mapea nombres de GET (snake_case) a nombres de datos (PascalCase/CamelCase).
 */
function filterRecordsCaseInsensitive(array $records): array
{
    // Mapeo entre nombre de filtro GET y nombre de campo en datos
    $fieldMap = [
        'ocr_code'         => 'OcrCode',
        'descripcion'      => 'Descripcion',
        'comments_factura' => 'CommentsFactura',
    ];

    // Construir lista de filtros no vacíos
    $filters = [];
    foreach ($fieldMap as $getName => $dataField) {
        $v = getParam($getName);
        if ($v !== null && $v !== '') {
            $filters[$dataField] = $v;
        }
    }

    if (empty($filters)) {
        return $records;
    }

    $out = [];
    foreach ($records as $row) {
        $match = true;
        foreach ($filters as $dataField => $val) {
            $hay = (string) ($row[$dataField] ?? '');
            if (strcasecmp($hay, $val) !== 0) {
                $match = false;
                break;
            }
        }
        if ($match) {
            $out[] = $row;
        }
    }

    return $out;
}

/**
 * Extrae valores únicos de un campo de un array de registros.
 * Devuelve array ordenado sin duplicados.
 */
function getUniqueValues(array $records, string $field): array
{
    $values = [];
    foreach ($records as $row) {
        $val = trim($row[$field] ?? '');
        if ($val !== '') {
            $values[$val] = true;
        }
    }
    $values = array_keys($values);
    sort($values);
    return $values;
}
