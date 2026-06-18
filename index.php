<?php

declare(strict_types=1);

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/api.php';

$currentPage = max(1, (int) getParam('page', '1'));
$limit       = normalizeLimit(getParam('limit'));

$textFilters = ['ocr_code', 'comments_pago', 'comments_factura'];
$hasTextFilter = false;
foreach ($textFilters as $tf) {
    if (getParam($tf) !== null && getParam($tf) !== '') {
        $hasTextFilter = true;
        break;
    }
}

// If textual filters are present, fetch ALL pages from the API without sending
// the textual filters, then apply client-side, case-insensitive filtering in PHP.
// This keeps the table total aligned with the API when matches are beyond the first page.
if ($hasTextFilter) {
    $apiData = fetchAllApiPages([
        'ocr_code'         => '',
        'comments_pago'    => '',
        'comments_factura' => '',
    ]);
} else {
    $apiUrl  = buildApiUrl(['page' => (string) $currentPage, 'limit' => (string) $limit]);
    $apiData = callApi($apiUrl);
}

$hasError     = false;
$errorMessage = '';
$data         = [];
$total        = 0;
$pagesTotal   = 0;
$recordCount  = 0;
$recordsData  = [];

if ($apiData === null) {
    $hasError     = true;
    $errorMessage = 'No se pudo conectar con el servidor';
} elseif (isset($apiData['ok']) && $apiData['ok'] === false) {
    $hasError     = true;
    $errorMessage = $apiData['error'] ?? 'Error desconocido';
} elseif (isset($apiData['ok']) && $apiData['ok'] === true) {
    $data        = $apiData;
    $recordsData = $data['data'] ?? [];

    // Apply client-side case-insensitive filtering when textual filters were used
    if ($hasTextFilter) {
        $filtered = filterRecordsCaseInsensitive($recordsData);
        $total = count($filtered);
        $pagesTotal = (int) ceil($total / $limit);
        $recordCount = $total;
        // Paginate the filtered results for current page
        $offset = ($currentPage - 1) * $limit;
        $recordsData = array_slice($filtered, $offset, $limit);
    } else {
        $total       = (int) ($data['total'] ?? 0);
        $pagesTotal  = (int) ($data['pages_total'] ?? 0);
        $recordCount = (int) ($data['records_count'] ?? 0);
    }
} else {
    $hasError     = true;
    $errorMessage = 'Formato de respuesta inesperado';
}

$rangeStart = $total > 0 ? (($currentPage - 1) * $limit) + 1 : 0;
$rangeEnd   = min($currentPage * $limit, $total);

$pageSum = 0.0;
foreach ($recordsData as $row) {
    $pageSum += (float) ($row['GTotal'] ?? 0);
}

$activeFilters = getActiveFilters();

// Fetch ALL records for filter dropdowns - paginate through all pages to get ALL unique values.
// When textual filters are used, the main API call already fetched all pages before filtering.
if ($hasTextFilter) {
    $allRecordsForFilterDropdowns = $data['data'] ?? [];
} else {
    $dropdownData = fetchAllApiPages();
    $allRecordsForFilterDropdowns = ($dropdownData['ok'] ?? false) ? ($dropdownData['data'] ?? []) : [];
}

$uniqueDescriptions = getUniqueValues($allRecordsForFilterDropdowns, 'CommentsPago');
$uniqueOcrCodes = getUniqueValues($allRecordsForFilterDropdowns, 'OcrCode');
$uniqueComments = getUniqueValues($allRecordsForFilterDropdowns, 'CommentsFactura');

require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/filter-panel.php';
require __DIR__ . '/templates/active-filters.php';
require __DIR__ . '/templates/info-bar.php';
require __DIR__ . '/templates/table.php';
require __DIR__ . '/templates/pagination.php';
require __DIR__ . '/templates/footer.php';
