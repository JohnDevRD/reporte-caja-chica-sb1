<?php

declare(strict_types=1);

?>
<div class="row row-cols-2 row-cols-md-4 g-2 mb-3">
    <?php foreach ($sucursalTotales as $sucursal => $total): ?>
        <div class="col">
            <div class="card border-0 shadow-sm h-100" style="background: #f0f4ff;">
                <div class="card-body text-center py-2 px-3">
                    <div class="small text-muted text-uppercase fw-semibold mb-1"><?php echo html($sucursal); ?></div>
                    <div class="fs-5 fw-bold text-primary">$ <?php echo formatCurrency((string) $total); ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="col">
        <div class="card border-0 shadow-sm h-100" style="background: #f0faf0;">
            <div class="card-body text-center py-2 px-3">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Total General</div>
                <div class="fs-5 fw-bold text-success">$ <?php echo formatCurrency((string) $montoTotalGeneral); ?></div>
            </div>
        </div>
    </div>
</div>
