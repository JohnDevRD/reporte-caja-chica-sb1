<?php if (!$hasError && $pagesTotal > 1): ?>
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-3 px-3 py-3 bg-white rounded shadow-sm">
    <div class="small text-secondary">
        Página <strong class="text-dark"><?php echo $currentPage; ?></strong> de <strong class="text-dark"><?php echo $pagesTotal; ?></strong>
    </div>

    <nav aria-label="Navegación de páginas">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo buildQueryString(['page' => '1']); ?>" aria-label="Primera">&laquo;</a>
            </li>
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo buildQueryString(['page' => (string) ($currentPage - 1)]); ?>" aria-label="Anterior">&lsaquo;</a>
            </li>

            <?php
            $startPage = max(1, $currentPage - 3);
            $endPage   = min($pagesTotal, $currentPage + 3);

            if ($startPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo buildQueryString(['page' => '1']); ?>">1</a>
                </li>
                <?php if ($startPage > 2): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif;
            endif; ?>

            <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                    <?php if ($p === $currentPage): ?>
                        <span class="page-link"><?php echo $p; ?></span>
                    <?php else: ?>
                        <a class="page-link" href="?<?php echo buildQueryString(['page' => (string) $p]); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>

            <?php if ($endPage < $pagesTotal):
                if ($endPage < $pagesTotal - 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo buildQueryString(['page' => (string) $pagesTotal]); ?>"><?php echo $pagesTotal; ?></a>
                </li>
            <?php endif; ?>

            <li class="page-item <?php echo $currentPage >= $pagesTotal ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo buildQueryString(['page' => (string) ($currentPage + 1)]); ?>" aria-label="Siguiente">&rsaquo;</a>
            </li>
            <li class="page-item <?php echo $currentPage >= $pagesTotal ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?php echo buildQueryString(['page' => (string) $pagesTotal]); ?>" aria-label="Última">&raquo;</a>
            </li>
        </ul>
    </nav>

    <div class="d-flex align-items-center gap-2 small">
        <label for="pageSize" class="text-secondary mb-0">Mostrar:</label>
        <select id="pageSize" class="form-select form-select-sm" style="width: auto;">
            <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50</option>
            <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100</option>
            <option value="200" <?php echo $limit === 200 ? 'selected' : ''; ?>>200</option>
            <option value="500" <?php echo $limit === 500 ? 'selected' : ''; ?>>500</option>
        </select>
    </div>
</div>
<?php endif; ?>
