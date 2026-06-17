(function () {
    'use strict';

    var loader = document.getElementById('loader');

    function showLoader() {
        if (loader) {
            loader.classList.remove('d-none');
            loader.classList.add('d-flex');
        }
    }

    function hideLoader() {
        if (loader) {
            loader.classList.remove('d-flex');
            loader.classList.add('d-none');
        }
    }

    var filterBody = document.getElementById('filterBody');
    var filterIcon = document.getElementById('filterIcon');
    var filterCardHeader = filterBody ? document.querySelector('[data-bs-target="#filterBody"]') : null;

    if (filterBody && filterIcon) {
        filterBody.addEventListener('shown.bs.collapse', function () {
            filterIcon.innerHTML = '<i class="bi bi-chevron-up"></i>';
            if (filterCardHeader) filterCardHeader.setAttribute('aria-expanded', 'true');
        });
        filterBody.addEventListener('hidden.bs.collapse', function () {
            filterIcon.innerHTML = '<i class="bi bi-chevron-down"></i>';
            if (filterCardHeader) filterCardHeader.setAttribute('aria-expanded', 'false');
        });
    }

    var btnClear = document.getElementById('btnClear');
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            window.location.search = '';
        });
    }

    function getQueryParam(key) {
        return new URLSearchParams(window.location.search).get(key) || '';
    }

    function setQueryParam(key, value) {
        var params = new URLSearchParams(window.location.search);
        if (value === '' || value === null || value === undefined) {
            params.delete(key);
        } else {
            params.set(key, value);
        }
        return params.toString();
    }

    function navigateWithParams(params) {
        window.location.search = params;
    }

    document.querySelectorAll('[data-filter]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = this.getAttribute('data-filter');
            navigateWithParams(setQueryParam(key, ''));
        });
    });

    var pageSizeSelect = document.getElementById('pageSize');
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function () {
            var params = new URLSearchParams(window.location.search);
            params.set('limit', this.value);
            params.set('page', '1');
            navigateWithParams(params.toString());
        });
    }



    var filterForm = filterBody ? filterBody.querySelector('form') : null;
    if (filterForm) {
        filterForm.addEventListener('submit', function () {
            showLoader();
        });
    }

    // Inicializar Select2 para filtros con muchos elementos
    jQuery(document).ready(function() {
        jQuery('#descripcion').select2({
            placeholder: 'Selecciona una descripción...',
            allowClear: true,
            width: '100%'
        });
        
        jQuery('#comments_factura').select2({
            placeholder: 'Selecciona comentarios...',
            allowClear: true,
            width: '100%'
        });

        jQuery('#descripcion, #comments_factura').on('select2:clear', function() {
            jQuery(this).closest('form').trigger('submit');
        });
    });

    hideLoader();
})();
