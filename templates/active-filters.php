<?php if (count($activeFilters) > 0): ?>
<div class="d-flex flex-wrap gap-2 mb-3" id="activeFilters">
    <?php foreach ($activeFilters as $filter): ?>
        <span class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill small fw-normal" style="background: #edf2ff; color: #364fc7; border: 1px solid #bac8ff;">
            <span class="fw-semibold" style="color: #5c7cfa;"><?php echo html($filter['label']); ?>:</span>
            <?php echo html($filter['value']); ?>
            <button class="btn-close" data-filter="<?php echo html($filter['key']); ?>" style="width: 0.5rem; height: 0.5rem; font-size: 0.6rem;" title="Quitar filtro"></button>
        </span>
    <?php endforeach; ?>
</div>
<?php endif; ?>
