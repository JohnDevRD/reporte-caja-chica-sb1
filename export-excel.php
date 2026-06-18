<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/api.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

$textFilters = ['ocr_code', 'comments_pago', 'comments_factura'];
$hasTextFilter = false;
foreach ($textFilters as $tf) {
    if (getParam($tf) !== null && getParam($tf) !== '') {
        $hasTextFilter = true;
        break;
    }
}

$records = [];
$total   = 0;

if ($hasTextFilter) {
    $pageData = fetchAllApiPages([
        'ocr_code'         => '',
        'comments_pago'    => '',
        'comments_factura' => '',
    ]);
    if ($pageData && isset($pageData['ok']) && $pageData['ok'] === true) {
        $records = $pageData['data'] ?? [];
        $records = filterRecordsCaseInsensitive($records);
        $total = count($records);
    }
} else {
    $apiUrl  = buildApiUrl(['page' => '1', 'limit' => '500']);
    $apiData = callApi($apiUrl);

    if ($apiData && isset($apiData['ok']) && $apiData['ok'] === true) {
        $records   = $apiData['data'] ?? [];
        $total     = (int) ($apiData['total'] ?? 0);
        $pagesTotal = (int) ($apiData['pages_total'] ?? 1);

        for ($page = 2; $page <= $pagesTotal; $page++) {
            $pageUrl  = buildApiUrl(['page' => (string) $page, 'limit' => '500']);
            $pageData = callApi($pageUrl);
            if ($pageData && isset($pageData['ok']) && $pageData['ok'] === true) {
                $records = array_merge($records, $pageData['data'] ?? []);
            }
        }
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte Caja Chica');

$headers = [
    'A1' => '#',
    'B1' => 'Fecha',
    'C1' => 'Descripción',
    'D1' => 'Sucursal',
    'E1' => 'Comentarios Factura',
    'F1' => 'Monto',
];

$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 10,
        'name' => 'Calibri',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1A2A6C'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'FFFFFF'],
        ],
    ],
];

foreach ($headers as $cell => $label) {
    $sheet->setCellValue($cell, $label);
}
$sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(22);

$evenRowStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'F0F4FF'],
    ],
];

$oddRowStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'FFFFFF'],
    ],
];

$cellStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'DDE1E6'],
        ],
    ],
];

$currencyStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'DDE1E6'],
        ],
    ],
    'numberFormat' => [
        'formatCode' => '#,##0.00',
    ],
];

$rowIdx = 2;
$totalSum = 0.0;

foreach ($records as $row) {
    $monto = (float) ($row['GTotal'] ?? 0);
    $totalSum += $monto;
    $tipo = strtoupper(trim($row['TipoDetalle'] ?? ''));

    $fill = $rowIdx % 2 === 0 ? $evenRowStyle['fill'] : $oddRowStyle['fill'];

    $sheet->setCellValue('A' . $rowIdx, $rowIdx - 1);
    $sheet->setCellValue('B' . $rowIdx, formatDate($row['DocDate'] ?? null));
    $sheet->setCellValue('C' . $rowIdx, $row['CommentsPago'] ?? '');
    $sheet->setCellValue('D' . $rowIdx, $row['OcrCode'] ?? '');
    $sheet->setCellValue('E' . $rowIdx, $row['CommentsFactura'] ?? '');
    $sheet->setCellValue('F' . $rowIdx, $monto);

    $sheet->getStyle('A' . $rowIdx)->applyFromArray(array_merge($cellStyle, ['fill' => $fill]));
    $sheet->getStyle('B' . $rowIdx . ':E' . $rowIdx)->applyFromArray(array_merge($cellStyle, ['fill' => $fill]));
    $sheet->getStyle('F' . $rowIdx)->applyFromArray(array_merge($currencyStyle, ['fill' => $fill]));

    $sheet->getRowDimension($rowIdx)->setRowHeight(18);

    $rowIdx++;
}

$totalRow = $rowIdx;
$sheet->setCellValue('A' . $totalRow, '');
$sheet->setCellValue('B' . $totalRow, '');
$sheet->setCellValue('C' . $totalRow, '');
$sheet->setCellValue('D' . $totalRow, '');
$sheet->setCellValue('E' . $totalRow, 'TOTAL');
$sheet->setCellValue('F' . $totalRow, $totalSum);

$totalStyle = [
    'font' => [
        'bold' => true,
        'size' => 10,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E2E8F0'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '1A2A6C'],
        ],
        'top' => [
            'borderStyle' => Border::BORDER_MEDIUM,
            'color' => ['rgb' => '1A2A6C'],
        ],
    ],
];

$totalCurrencyStyle = array_merge($totalStyle, [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'numberFormat' => [
        'formatCode' => '#,##0.00',
    ],
]);

$sheet->getStyle('A' . $totalRow . ':E' . $totalRow)->applyFromArray($totalStyle);
$sheet->getStyle('F' . $totalRow)->applyFromArray($totalCurrencyStyle);


$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(12);
$sheet->getColumnDimension('C')->setWidth(35);
$sheet->getColumnDimension('D')->setWidth(18);
$sheet->getColumnDimension('E')->setWidth(30);
$sheet->getColumnDimension('F')->setWidth(14);

$sheet->setSelectedCell('A1');

$writer = new Xlsx($spreadsheet);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_caja_chica_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
