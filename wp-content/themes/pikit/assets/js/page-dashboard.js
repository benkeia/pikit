(function () {
    var sidebar = document.getElementById('pk-dashboard-sidebar');
    var sidebarToggle = document.querySelector('[data-pikit-sidebar-toggle]');

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            var isOpen = sidebar.classList.toggle('is-open');
            sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    var forms = document.querySelectorAll('[data-pikit-rows-form]');

    forms.forEach(function (form) {
        var rowsContainer = form.querySelector('[data-pikit-rows]');
        var addButton = form.querySelector('[data-pikit-add-row]');

        if (!rowsContainer || !addButton) {
            return;
        }

        addButton.addEventListener('click', function () {
            var firstRow = rowsContainer.querySelector('[data-pikit-row]');
            if (!firstRow) {
                return;
            }

            var clone = firstRow.cloneNode(true);

            clone.querySelectorAll('input, select').forEach(function (field) {
                if (field.name === 'quantities[]') {
                    field.value = '1';
                } else {
                    field.value = '';
                }
            });

            rowsContainer.appendChild(clone);
        });

        rowsContainer.addEventListener('click', function (event) {
            var target = event.target;
            if (!(target instanceof HTMLElement) || !target.matches('[data-pikit-remove-row]')) {
                return;
            }

            var rows = rowsContainer.querySelectorAll('[data-pikit-row]');
            if (rows.length <= 1) {
                return;
            }

            var row = target.closest('[data-pikit-row]');
            if (row) {
                row.remove();
            }
        });
    });

    var deleteForms = document.querySelectorAll('[data-pikit-confirm-delete]');
    deleteForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var accepted = window.confirm('Supprimer ce brouillon ? Cette action est définitive.');
            if (!accepted) {
                event.preventDefault();
            }
        });
    });
}());
