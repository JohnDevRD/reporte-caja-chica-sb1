<?php

declare(strict_types=1);

ini_set('memory_limit', '256M');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/api.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$maxPdfRows = 200;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultPaperOrientation', 'landscape');
$options->set('defaultFont', 'Helvetica');
$options->set('debugPng', false);
$options->set('debugKeepTemp', false);
$options->set('isFontSubsettingEnabled', true);

$dompdf = new Dompdf($options);

$records   = [];
$total     = 0;
$errorMsg  = '';
$truncated = false;

// Check if textual filters are present
$textFilters = ['ocr_code', 'descripcion', 'comments_factura'];
$hasTextFilter = false;
foreach ($textFilters as $tf) {
    if (getParam($tf) !== null && getParam($tf) !== '') {
        $hasTextFilter = true;
        break;
    }
}

if ($hasTextFilter) {
    // Fetch ALL pages without textual filters, then apply client-side filtering.
    $apiData = fetchAllApiPages([
        'ocr_code'         => '',
        'descripcion'      => '',
        'comments_factura' => '',
    ]);
    
    if ($apiData && isset($apiData['ok']) && $apiData['ok'] === true) {
        $records = $apiData['data'] ?? [];
        $records = filterRecordsCaseInsensitive($records);
        $total = count($records);
        $truncated = $total > $maxPdfRows;
    } elseif ($apiData === null) {
        $errorMsg = 'No se pudo conectar con el servidor';
    } elseif (isset($apiData['ok']) && $apiData['ok'] === false) {
        $errorMsg = $apiData['error'] ?? 'Error desconocido';
    } else {
        $errorMsg = 'Formato de respuesta inesperado';
    }
} else {
    // No textual filters: use normal API call with all parameters
    $apiUrl  = buildApiUrl(['page' => '1', 'limit' => (string) $maxPdfRows]);
    $apiData = callApi($apiUrl);
    
    if ($apiData === null) {
        $errorMsg = 'No se pudo conectar con el servidor';
    } elseif (isset($apiData['ok']) && $apiData['ok'] === false) {
        $errorMsg = $apiData['error'] ?? 'Error desconocido';
    } elseif (isset($apiData['ok']) && $apiData['ok'] === true) {
        $records = $apiData['data'] ?? [];
        $total   = (int) ($apiData['total'] ?? 0);
        $truncated = $total > $maxPdfRows;
    } else {
        $errorMsg = 'Formato de respuesta inesperado';
    }
}

$desde   = getParam('desde', '');
$hasta   = getParam('hasta', '');
$fechaGen = date('d/m/Y H:i');

function p(string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 20mm 15mm 25mm 15mm;
    }

    body {
        font-family: Helvetica, sans-serif;
        font-size: 9pt;
        color: #1a1a2e;
        line-height: 1.4;
    }

    .header {
        text-align: center;
        padding-bottom: 12px;
        border-bottom: 3px solid #1a2a6c;
        margin-bottom: 16px;
    }

    .header h1 {
        font-size: 18pt;
        color: #1a2a6c;
        margin: 0 0 4px 0;
        letter-spacing: 1px;
    }

    .header .subtitle {
        font-size: 10pt;
        color: #5a5a7a;
        margin: 0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        font-size: 8.5pt;
        color: #444;
        margin-bottom: 14px;
        padding: 8px 10px;
        background: #f0f4ff;
        border-radius: 4px;
    }

    .info-row table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-row td {
        padding: 2px 8px;
        vertical-align: top;
    }

    .info-label {
        font-weight: bold;
        color: #1a2a6c;
    }

    table.data {
        width: 100%;
        border-collapse: collapse;
        font-size: 7.5pt;
    }

    table.data thead th {
        background: #1a2a6c;
        color: #fff;
        padding: 6px 5px;
        text-align: center;
        font-weight: 600;
        font-size: 7pt;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #1a2a6c;
    }

    table.data tbody td {
        padding: 4px 5px;
        text-align: center;
        border: 1px solid #dde1e6;
        vertical-align: middle;
    }

    table.data tbody tr:nth-child(even) {
        background: #f8f9fc;
    }

    table.data tbody tr.row-factura {
        background: #f0f4ff;
    }

    table.data tbody tr.row-cuenta {
        background: #fffbe6;
    }

    .col-monto {
        text-align: center;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .table-footer {
        margin-top: 10px;
        text-align: right;
        font-size: 9pt;
        font-weight: bold;
        padding: 8px 10px;
        background: #f0f4ff;
        border-top: 2px solid #1a2a6c;
    }

    .truncated-note {
        margin-top: 6px;
        font-size: 7.5pt;
        color: #c00;
        text-align: center;
    }

    .footer-note {
        margin-top: 20px;
        text-align: center;
        font-size: 7.5pt;
        color: #999;
        border-top: 1px solid #ddd;
        padding-top: 6px;
    }

    .error-box {
        padding: 30px;
        text-align: center;
        font-size: 11pt;
        color: #c00;
    }
</style>
</head>
<body>';

if ($errorMsg) {
    $html .= '<div class="error-box">' . p($errorMsg) . '</div>';
} elseif ($total === 0) {
    $html .= '<div class="error-box">No se encontraron registros para los filtros seleccionados.</div>';
} else {
    $totalSum = 0.0;
    foreach ($records as $row) {
        $totalSum += (float) ($row['GTotal'] ?? 0);
    }

    $filtrosStr = '';
    $filterRanges = [
        'desde' => 'Fecha pago desde', 'hasta' => 'Fecha pago hasta',
    ];
    foreach ($filterRanges as $key => $label) {
        $v = getParam($key);
        if ($v) {
            $filtrosStr .= '<tr><td class="info-label">' . $label . ':</td><td>' . formatDate($v) . '</td></tr>';
        }
    }
    $filterFields = [
        'ocr_code' => 'Sucursal', 'descripcion' => 'Descripción',
    ];
    foreach ($filterFields as $key => $label) {
        $v = getParam($key);
        if ($v) {
            $filtrosStr .= '<tr><td class="info-label">' . $label . ':</td><td>' . p($v) . '</td></tr>';
        }
    }

    $html .= '
    <div class="header">
        <h1>REPORTE DE CAJA CHICA</h1>
        <p class="subtitle">Generado el ' . $fechaGen . '</p>
    </div>

    <div class="info-row">
        <table>
            <tr>
                <td class="info-label">Total registros:</td>
                <td>' . number_format($total) . ($truncated ? '*' : '') . '</td>
                <td class="info-label">Total mostrado:</td>
                <td>$ ' . number_format($totalSum, 2, '.', ',') . '</td>
            </tr>' . $filtrosStr . '
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 20px;">#</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Sucursal</th>
                <th>Comentarios Factura</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>';

    $idx = 1;
    $pageSum = 0.0;
    foreach ($records as $row) {
        $tipo = strtoupper(trim($row['TipoDetalle'] ?? ''));
        $rowClass = $tipo === 'FACTURA' ? 'row-factura' : 'row-cuenta';
        $monto = (float) ($row['GTotal'] ?? 0);
        $pageSum += $monto;

        $html .= '
            <tr class="' . $rowClass . '">
                <td>' . $idx++ . '</td>
                <td>' . formatDate($row['DocDate'] ?? null) . '</td>
                <td>' . p($row['Descripcion'] ?? '') . '</td>
                <td>' . p($row['OcrCode'] ?? '') . '</td>
                <td>' . p($row['CommentsFactura'] ?? '') . '</td>
                <td class="col-monto">$ ' . number_format($monto, 2, '.', ',') . '</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>

    <div class="table-footer">
        Subtotal: $ ' . number_format($pageSum, 2, '.', ',') . '
    </div>
    ' . ($truncated ? '<div class="truncated-note">* Se muestran solo los primeros ' . $maxPdfRows . ' de ' . number_format($total) . ' registros. Ajuste los filtros para un reporte más específico.</div>' : '') . '';
}

$html .= '
    <div class="footer-note">
        ' . APP_NAME . ' &mdash; Documento generado el ' . $fechaGen . ' &mdash; Pág. 1
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream('reporte_caja_chica_' . date('Y-m-d') . '.pdf', [
    'Attachment' => false,
]);
