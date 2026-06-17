<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
    <div class="small text-secondary">
        <?php if (!$hasError && $total > 0): ?>
            Mostrando <strong class="text-dark"><?php echo number_format($rangeStart); ?>–<?php echo number_format($rangeEnd); ?></strong> de <strong class="text-dark"><?php echo number_format($total); ?></strong> registros
        <?php elseif (!$hasError && $total === 0): ?>
            No se encontraron registros
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <a href="export-excel.php?<?php echo htmlspecialchars(buildQueryString()); ?>" class="btn btn-success btn-sm" id="btnExportExcel" <?php echo ($hasError || $total === 0) ? 'style="pointer-events: none; opacity: 0.5;"' : ''; ?>>
            <i class="bi bi-download"></i> Exportar Excel
        </a>
        <a href="generate-pdf.php?<?php echo htmlspecialchars(buildQueryString()); ?>" class="btn btn-outline-secondary btn-sm" id="btnPrint" <?php echo ($hasError || $total === 0) ? 'style="pointer-events: none; opacity: 0.5;"' : ''; ?> target="_blank">
            <i class="bi bi-printer"></i> Imprimir
        </a>
    </div>
</div>
