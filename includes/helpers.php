<?php

declare(strict_types=1);

function getParam(string $key, ?string $default = null): ?string
{
    $value = $_GET[$key] ?? null;
    if (!is_string($value) || $value === '') {
        return $default;
    }
    return trim($value);
}

function getParamArray(string $key): array
{
    $value = $_GET[$key] ?? [];
    if (!is_array($value)) {
        return [];
    }
    return array_values(array_filter(array_map('trim', $value), fn($v) => $v !== ''));
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
        'ocr_code', 'categoria', 'comments_factura',
        'page', 'limit'
    ];

    $params = [];
    foreach ($fields as $field) {
        // Intentar primero como array, luego como string simple
        $value = $overrides[$field] ?? getParamArray($field);

        if (is_array($value) && !empty($value)) {
            $params[$field] = $value; // http_build_query maneja arrays correctamente
        } else {
            $single = $overrides[$field] ?? getParam($field);
            if ($single !== null && $single !== '') {
                $params[$field] = $single;
            }
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
        'categoria'        => 'Categoría',
        'comments_factura' => 'Comentarios Factura',
    ];

    foreach ($filterLabels as $key => $label) {
        // Intentar como array primero
        $arrVal = getParamArray($key);
        if (!empty($arrVal)) {
            $displayVal = implode(', ', $arrVal);
            if (in_array($key, ['desde', 'hasta'], true)) {
                $displayVal = implode(', ', array_map('formatDate', $arrVal));
            }
            $activeFilters[] = ['key' => $key, 'label' => $label, 'value' => $displayVal];
        } else {
            $val = getParam($key);
            if ($val !== null && $val !== '') {
                $displayVal = $val;
                if (in_array($key, ['desde', 'hasta'], true)) {
                    $displayVal = formatDate($val);
                }
                $activeFilters[] = ['key' => $key, 'label' => $label, 'value' => $displayVal];
            }
        }
    }

    return $activeFilters;
}

/**
 * Filtra registros (array de filas) aplicando filtros de texto en forma
 * case-insensitive. Para cada campo de filtro textual presente en GET,
 * realiza una comparación exacta case-insensitive (strcasecmp) contra
 * el valor del registro. Devuelve solo los registros que cumplen con
 * todos los filtros textuales.
 * 
 * Mapea nombres de GET (snake_case) a nombres de datos (PascalCase/CamelCase).
 */
function filterRecordsCaseInsensitive(array $records): array
{
    // Mapeo entre nombre de filtro GET y nombre de campo en datos
    $fieldMap = [
        'ocr_code'         => 'OcrCode',
        'categoria'        => 'Categoria',
        'comments_factura' => 'CommentsFactura',
    ];

    // Construir lista de filtros no vacíos
    $filters = [];

    foreach ($fieldMap as $getName => $dataField) {
        // Intentar como array (multi-select)
        $arrVal = getParamArray($getName);
        if (!empty($arrVal)) {
            $filters[$dataField] = $arrVal; // guarda array
        } else {
            $v = getParam($getName);
            if ($v !== null && $v !== '') {
                $filters[$dataField] = [$v]; // normalizar a array de 1 elemento
            }
        }
    }

    if (empty($filters)) {
        return $records;
    }

    $out = [];
    foreach ($records as $row) {
        $match = true;
        foreach ($filters as $dataField => $allowedValues) {
            $hay = (string) ($row[$dataField] ?? '');
            // Verificar si el valor del registro está dentro de los permitidos
            $found = false;
            foreach ($allowedValues as $allowed) {
                if (strcasecmp($hay, $allowed) === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
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
