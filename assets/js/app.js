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

    function setQueryParam(key, value) {
        var params = new URLSearchParams(window.location.search);
        if (value === '' || value === null || value === undefined) {
            params.delete(key);
            params.delete(key + '[]');
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

    function normalizeSearchText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function matchCustom(params, data) {
        if (jQuery.trim(params.term) === '') {
            return data;
        }

        if (typeof data.text === 'undefined') {
            return null;
        }

        var term = normalizeSearchText(params.term);
        var text = normalizeSearchText(data.text);

        if (text.indexOf(term) !== -1) {
            return data;
        }

        return null;
    }

    function getMatchingOptions($select, searchTerm) {
        var normalizedTerm = normalizeSearchText(searchTerm);
        var matches = [];

        if (normalizedTerm === '') {
            return matches;
        }

        $select.find('option').each(function () {
            var $option = $(this);
            var value = $option.val();

            if (value === '') {
                return;
            }

            if (normalizeSearchText($option.text()).indexOf(normalizedTerm) !== -1) {
                matches.push($option);
            }
        });

        return matches;
    }

    function injectSelectAll($select) {
        var select2 = $select.data('select2');
        if (!select2 || !select2.$dropdown) return;

        var $dropdown = select2.$dropdown;
        // In multiple selects, the search field is in the container, not the dropdown.
        var $search = select2.$container.find('.select2-search__field');
        if ($search.length === 0) {
            $search = $dropdown.find('.select2-search__field');
        }
        var $results = $dropdown.find('.select2-results__options');

        $dropdown.find('.select-all-matching').remove();

        if ($search.length === 0 || $results.length === 0) return;

        var searchTerm = jQuery.trim($search.val());
        var matches = getMatchingOptions($select, searchTerm);
        if (matches.length === 0) return;

        var selectedMatches = matches.filter(function ($option) {
            return $option.prop('selected');
        }).length;
        var pending = matches.length - selectedMatches;
        var label = pending === 0
            ? 'Todos los ' + matches.length + ' resultados ya están seleccionados'
            : 'Seleccionar ' + pending + ' de ' + matches.length + ' resultados que contienen "' + searchTerm + '"';

        var $button = jQuery('<li class="select2-results__option select-all-matching" role="option"></li>')
            .text(label)
            .css({
                'border-bottom': '1px solid #dee2e6',
                'color': pending === 0 ? '#2e7d32' : '#0d6efd',
                'cursor': pending === 0 ? 'default' : 'pointer',
                'font-weight': '600',
                'background': pending === 0 ? '#e8f5e9' : '#e3f2fd'
            });

        if (pending > 0) {
            $button.on('mouseup click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                matches.forEach(function ($option) {
                    $option.prop('selected', true);
                });

                $select.trigger('change');
                injectSelectAll($select);
            });
        }

        $results.prepend($button);
    }

    jQuery(document).ready(function() {
        var selectIds = ['#categoria', '#ocr_code', '#comments_factura'];

        jQuery.each(selectIds, function (i, id) {
            jQuery(id).select2({
                placeholder: id === '#categoria' ? 'Selecciona una categoria...' :
                             id === '#ocr_code' ? 'Selecciona sucursales...' :
                                                  'Selecciona comentarios...',
                allowClear: true,
                width: '100%',
                multiple: true,
                matcher: matchCustom
            });
        });

        jQuery('#categoria, #ocr_code, #comments_factura').on('select2:clear', function() {
            jQuery(this).closest('form').trigger('submit');
        });

        jQuery(document).on('select2:open', function (e) {
            var $target = jQuery(e.target);
            if (!$target.is('.select2-hidden-accessible')) return;

            var $select = $target;
            var refreshSelectAll = function () {
                window.setTimeout(function () {
                    injectSelectAll($select);
                }, 50);
            };

            refreshSelectAll();

            var select2 = $select.data('select2');
            if (!select2 || !select2.$dropdown) return;

            var $search = select2.$container.find('.select2-search__field');
            if ($search.length === 0) {
                $search = select2.$dropdown.find('.select2-search__field');
            }

            $search
                .off('input.selectAll keyup.selectAll')
                .on('input.selectAll keyup.selectAll', refreshSelectAll);

            var $results = select2.$dropdown.find('.select2-results__options');
            if ($results.length) {
                var observer = new MutationObserver(function (mutations) {
                    var shouldRefresh = false;
                    for (var i = 0; i < mutations.length; i++) {
                        var added = mutations[i].addedNodes;
                        var removed = mutations[i].removedNodes;
                        
                        // Check if the only thing added/removed is our own custom option
                        var onlyOurChanges = true;
                        
                        if (added.length > 0) {
                            for (var j = 0; j < added.length; j++) {
                                if (!jQuery(added[j]).hasClass('select-all-matching')) {
                                    onlyOurChanges = false;
                                }
                            }
                        }
                        
                        if (removed.length > 0) {
                            for (var k = 0; k < removed.length; k++) {
                                if (!jQuery(removed[k]).hasClass('select-all-matching')) {
                                    onlyOurChanges = false;
                                }
                            }
                        }
                        
                        if (!onlyOurChanges) {
                            shouldRefresh = true;
                            break;
                        }
                    }
                    
                    if (shouldRefresh) {
                        refreshSelectAll();
                    }
                });
                observer.observe($results[0], { childList: true });
                select2.$dropdown.data('selectAllObserver', observer);
            }
        });

        jQuery(document).on('select2:close', function (e) {
            var $target = jQuery(e.target);
            if (!$target.is('.select2-hidden-accessible')) return;

            var select2 = $target.data('select2');
            if (select2 && select2.$dropdown) {
                var observer = select2.$dropdown.data('selectAllObserver');
                if (observer) {
                    observer.disconnect();
                    select2.$dropdown.removeData('selectAllObserver');
                }
            }
        });
    });

    hideLoader();
})();
