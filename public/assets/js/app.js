document.addEventListener('DOMContentLoaded', () => {
    const profileToggle = document.querySelector('[data-profile-toggle]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    const mobileMenuBtn = document.querySelector('[data-mobile-menu]');
    const sidebar = document.querySelector('[data-sidebar]');

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', () => {
            profileMenu.classList.toggle('open');
        });

        document.addEventListener('click', (event) => {
            if (!profileMenu.contains(event.target) && !profileToggle.contains(event.target)) {
                profileMenu.classList.remove('open');
            }
        });
    }

    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    const listFilters = document.querySelectorAll('[data-list-filter]');

    const normalizeText = (value) => String(value || '').trim().toLowerCase();
    const parseDateValue = (value) => {
        const text = String(value || '').trim();
        if (!text) {
            return null;
        }

        const normalized = text.length >= 10 ? text.slice(0, 10) : text;
        const date = new Date(`${normalized}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
            return null;
        }

        return date;
    };

    listFilters.forEach((wrapper) => {
        const tableId = wrapper.dataset.targetTable || '';
        if (!tableId) {
            return;
        }

        const table = document.getElementById(tableId);
        const tbody = table ? table.querySelector('tbody') : null;
        if (!table || !tbody) {
            return;
        }

        const rows = Array.from(tbody.querySelectorAll('tr'));
        if (rows.length === 0) {
            return;
        }

        const searchInput = wrapper.querySelector('[data-filter-search]');
        const filterSelect = wrapper.querySelector('[data-filter-select]');
        const fromInput = wrapper.querySelector('[data-filter-from]');
        const toInput = wrapper.querySelector('[data-filter-to]');
        const resetButton = wrapper.querySelector('[data-filter-reset]');

        const configuredSearchColumns = String(wrapper.dataset.searchColumns || '')
            .split(',')
            .map((index) => Number.parseInt(index.trim(), 10))
            .filter((index) => Number.isInteger(index) && index >= 0);
        const filterColumn = Number.parseInt(String(wrapper.dataset.filterColumn || ''), 10);
        const dateColumn = Number.parseInt(String(wrapper.dataset.dateColumn || ''), 10);
        const emptyMessage = String(wrapper.dataset.emptyMessage || 'No matching records found.');

        const emptyRow = document.createElement('tr');
        emptyRow.className = 'list-filter-empty-row hidden';
        emptyRow.innerHTML = `<td colspan="${rows[0].children.length}">${emptyMessage}</td>`;
        tbody.appendChild(emptyRow);

        const runFilter = () => {
            const searchTerm = normalizeText(searchInput ? searchInput.value : '');
            const selectedFilter = normalizeText(filterSelect ? filterSelect.value : '');
            const fromDate = parseDateValue(fromInput ? fromInput.value : '');
            const toDate = parseDateValue(toInput ? toInput.value : '');

            let visibleCount = 0;

            rows.forEach((row) => {
                const cells = Array.from(row.children);

                const searchableColumns = configuredSearchColumns.length > 0
                    ? configuredSearchColumns
                    : cells.map((_, index) => index);

                const searchPass = searchTerm === '' || searchableColumns.some((columnIndex) => {
                    const cell = cells[columnIndex];
                    return cell ? normalizeText(cell.textContent).includes(searchTerm) : false;
                });

                const filterPass = (() => {
                    if (selectedFilter === '' || !Number.isInteger(filterColumn) || filterColumn < 0) {
                        return true;
                    }

                    const cell = cells[filterColumn];
                    if (!cell) {
                        return false;
                    }

                    const filterValue = normalizeText(cell.dataset.filterValue || cell.textContent);
                    return filterValue === selectedFilter;
                })();

                const datePass = (() => {
                    if (!fromDate && !toDate) {
                        return true;
                    }

                    if (!Number.isInteger(dateColumn) || dateColumn < 0) {
                        return true;
                    }

                    const cell = cells[dateColumn];
                    const rowDate = parseDateValue(cell ? cell.dataset.dateValue || cell.textContent : '');
                    if (!rowDate) {
                        return false;
                    }

                    if (fromDate && rowDate < fromDate) {
                        return false;
                    }

                    if (toDate && rowDate > toDate) {
                        return false;
                    }

                    return true;
                })();

                const visible = searchPass && filterPass && datePass;
                row.classList.toggle('hidden', !visible);

                if (visible) {
                    visibleCount += 1;
                }
            });

            emptyRow.classList.toggle('hidden', visibleCount > 0);
        };

        if (searchInput) {
            searchInput.addEventListener('input', runFilter);
        }
        if (filterSelect) {
            filterSelect.addEventListener('change', runFilter);
        }
        if (fromInput) {
            fromInput.addEventListener('change', runFilter);
        }
        if (toInput) {
            toInput.addEventListener('change', runFilter);
        }
        if (resetButton) {
            resetButton.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                }
                if (filterSelect) {
                    filterSelect.value = '';
                }
                if (fromInput) {
                    fromInput.value = '';
                }
                if (toInput) {
                    toInput.value = '';
                }

                runFilter();
            });
        }

        runFilter();
    });
});
