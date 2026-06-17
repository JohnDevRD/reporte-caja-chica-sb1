<div class="card shadow-sm no-print">
    <?php if ($hasError): ?>
        <div class="card-body text-center py-5">
            <h5 class="text-danger">Error al cargar datos</h5>
            <p class="text-secondary small mb-0"><?php echo html($errorMessage); ?></p>
        </div>
    <?php elseif ($total === 0): ?>
        <div class="card-body text-center py-5">
            <div class="text-muted" style="font-size: 3rem; opacity: 0.4;"><i class="bi bi-inbox"></i></div>
            <h5 class="mt-2">Sin resultados</h5>
            <p class="text-secondary small mb-0">No se encontraron registros con los filtros aplicados.<br>Intente ajustar los criterios de búsqueda.</p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="table table-sm align-middle mb-0 small report-table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" class="text-center" style="width: 3rem;">#</th>
                        <th scope="col" class="text-center">Fecha</th>
                        <th scope="col" class="text-center">Descripción</th>
                        <th scope="col" class="text-center">Sucursal</th>
                        <th scope="col" class="text-center">Comentarios Factura</th>
                        <th scope="col" class="text-center">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rowNum = $rangeStart; ?>
                    <?php foreach ($recordsData as $row): ?>
                        <?php $tipo = strtoupper(trim($row['TipoDetalle'] ?? '')); ?>
                        <tr class="<?php echo $tipo === 'FACTURA' ? 'row-factura' : 'row-cuenta'; ?>">
                            <td class="text-secondary text-center"><?php echo $rowNum++; ?></td>
                            <td class="text-center text-nowrap"><?php echo formatDate($row['DocDate'] ?? null); ?></td>
                            <td class="text-center"><?php echo html($row['Descripcion'] ?? ''); ?></td>
                            <td class="text-center"><span class="report-badge"><?php echo html($row['OcrCode'] ?? ''); ?></span></td>
                            <td class="text-center text-truncate"><?php echo html($row['CommentsFactura'] ?? ''); ?></td>
                            <td class="text-center fw-semibold text-nowrap"><?php echo '$ ' . formatCurrency($row['GTotal'] ?? null); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="bg-light border-top px-3 py-2 d-flex justify-content-end fw-semibold small">
            Subtotal página: <span class="text-primary ms-1">$ <?php echo formatCurrency((string) $pageSum); ?></span>
        </div>
    <?php endif; ?>
</div>
