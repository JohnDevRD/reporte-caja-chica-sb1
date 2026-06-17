<?php

declare(strict_types=1);

$collapseShow = empty($_GET) ? (count($activeFilters) > 0 ? 'show' : '') : '';
?>
<section class="card mb-3 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between py-3 user-select-none" role="button" data-bs-toggle="collapse" data-bs-target="#filterBody" aria-expanded="<?php echo $collapseShow ? 'true' : 'false'; ?>">
        <h2 class="fs-6 fw-semibold mb-0 text-secondary">Filtros de búsqueda</h2>
        <span id="filterIcon" class="small text-secondary transition-rotate"><?php echo $collapseShow ? '<i class="bi bi-chevron-up"></i>' : '<i class="bi bi-chevron-down"></i>'; ?></span>
    </div>
    <div class="collapse <?php echo $collapseShow; ?>" id="filterBody">
        <div class="card-body">
            <form action="" method="GET">
                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="text-uppercase text-muted small fw-bold mb-0 pb-1 border-bottom">Fechas de pago</h6>
                    </div>
                    <div class="col-md-3">
                        <label for="desde" class="form-label small">Desde</label>
                        <input type="date" id="desde" name="desde" class="form-control form-control-sm" value="<?php echo html(getParam('desde', '')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="hasta" class="form-label small">Hasta</label>
                        <input type="date" id="hasta" name="hasta" class="form-control form-control-sm" value="<?php echo html(getParam('hasta', '')); ?>">
                    </div>

                    <div class="col-12 mt-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-0 pb-1 border-bottom">Descripción</h6>
                    </div>
                    <div class="col-md-4">
                        <label for="descripcion" class="form-label small">Descripción del movimiento</label>
                        <select id="descripcion" name="descripcion" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <?php foreach ($uniqueDescriptions as $desc): ?>
                                <option value="<?php echo html($desc); ?>" <?php echo getParam('descripcion') === $desc ? 'selected' : ''; ?>>
                                    <?php echo html($desc); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 mt-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-0 pb-1 border-bottom">Sucursal</h6>
                    </div>
                    <div class="col-md-4">
                        <label for="ocr_code" class="form-label small">Sucursal</label>
                        <select id="ocr_code" name="ocr_code" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <?php foreach ($uniqueOcrCodes as $ocr): ?>
                                <option value="<?php echo html($ocr); ?>" <?php echo getParam('ocr_code') === $ocr ? 'selected' : ''; ?>>
                                    <?php echo html($ocr); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 mt-3">
                        <h6 class="text-uppercase text-muted small fw-bold mb-0 pb-1 border-bottom">Comentarios Factura</h6>
                    </div>
                    <div class="col-md-4">
                        <label for="comments_factura" class="form-label small">Comentarios Factura</label>
                        <select id="comments_factura" name="comments_factura" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <?php foreach ($uniqueComments as $comment): ?>
                                <option value="<?php echo html($comment); ?>" <?php echo getParam('comments_factura') === $comment ? 'selected' : ''; ?>>
                                    <?php echo html($comment); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="hidden" id="limit" name="limit" value="<?php echo $limit; ?>">
                </div>

                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" id="btnClear">
                        <i class="bi bi-x-lg"></i> Limpiar filtros
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
