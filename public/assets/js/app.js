document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('is-ready');

    const mobileMenuBtn = document.querySelector('[data-mobile-menu]');
    const sidebar = document.querySelector('[data-sidebar]');

    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    const activeMenus = new Set();
    const closeAllMenus = () => {
        activeMenus.forEach((menu) => {
            menu.classList.remove('open');
        });
        activeMenus.clear();
    };

    document.querySelectorAll('[data-menu]').forEach((wrapper) => {
        const toggle = wrapper.querySelector('[data-menu-toggle]');
        const menu = wrapper.querySelector('[data-menu-list]');

        if (!toggle || !menu) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = menu.classList.contains('open');
            closeAllMenus();
            if (!isOpen) {
                menu.classList.add('open');
                activeMenus.add(menu);
            }
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', () => closeAllMenus());

    let activeModal = null;
    let lastFocused = null;

    const getFocusable = (container) => Array.from(
        container.querySelectorAll(
            'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
        )
    ).filter((el) => !el.hasAttribute('disabled') && !el.getAttribute('aria-hidden') && (el.offsetParent !== null || el === document.activeElement));

    const focusFirst = (modal) => {
        const focusables = getFocusable(modal);
        if (focusables.length > 0) {
            focusables[0].focus();
        } else {
            modal.setAttribute('tabindex', '-1');
            modal.focus();
        }
    };

    const openModal = (modal) => {
        if (!modal) {
            return;
        }
        lastFocused = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        activeModal = modal;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');
        document.body.classList.add('modal-open');
        focusFirst(modal);
    };

    const closeModal = (modal) => {
        if (!modal) {
            return;
        }
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
        if (!document.querySelector('.modal.open')) {
            document.body.classList.remove('modal-open');
        }
        if (activeModal === modal) {
            activeModal = null;
        }
        if (lastFocused && document.contains(lastFocused)) {
            lastFocused.focus();
        }
    };

    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const targetId = trigger.dataset.modalOpen;
            if (!targetId) {
                return;
            }
            const modal = document.getElementById(targetId);
            openModal(modal);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            closeModal(trigger.closest('.modal'));
        });
    });

    document.querySelectorAll('.modal').forEach((modal) => {
        modal.setAttribute('aria-hidden', 'true');
        modal.addEventListener('click', (event) => {
            if (event.target === modal && !modal.hasAttribute('data-modal-static')) {
                closeModal(modal);
            }
        });
        if (modal.hasAttribute('data-modal-autoshow')) {
            openModal(modal);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const modal = document.querySelector('.modal.open');
            if (modal) {
                closeModal(modal);
            }
            closeAllMenus();
            return;
        }

        if (event.key !== 'Tab' || !activeModal) {
            return;
        }

        const focusables = getFocusable(activeModal);
        if (focusables.length === 0) {
            event.preventDefault();
            activeModal.focus();
            return;
        }

        const first = focusables[0];
        const last = focusables[focusables.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    const toastContainer = document.querySelector('[data-toast-container]');
    if (toastContainer) {
        const toasts = Array.from(toastContainer.querySelectorAll('[data-toast]'));
        toasts.forEach((toast) => {
            setTimeout(() => {
                toast.remove();
            }, 4200);
        });
    }

    const confirmModal = document.querySelector('[data-confirm-modal]');
    if (confirmModal) {
        const confirmTitle = confirmModal.querySelector('[data-confirm-title]');
        const confirmMessage = confirmModal.querySelector('[data-confirm-message]');
        const confirmAccept = confirmModal.querySelector('[data-confirm-accept]');
        let confirmAction = null;

        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const message = form.dataset.confirm || 'Are you sure you want to proceed?';
                if (confirmMessage) {
                    confirmMessage.textContent = message;
                }
                if (confirmTitle) {
                    confirmTitle.textContent = form.dataset.confirmTitle || 'Confirm Action';
                }
                confirmAction = () => form.submit();
                openModal(confirmModal);
            });
        });

        if (confirmAccept) {
            confirmAccept.addEventListener('click', () => {
                if (confirmAction) {
                    confirmAction();
                    confirmAction = null;
                }
                closeModal(confirmModal);
            });
        }
    }

    const storageGet = (key) => {
        try {
            return window.localStorage.getItem(key);
        } catch (error) {
            return null;
        }
    };

    const storageSet = (key, value) => {
        try {
            window.localStorage.setItem(key, value);
        } catch (error) {
            return;
        }
    };

    const tableStates = new Map();

    const applyColumnVisibility = (state) => {
        const hiddenColumns = state.hiddenColumns || new Set();
        state.headers.forEach((_, index) => {
            const hide = hiddenColumns.has(index);
            const th = state.table.querySelector(`thead th:nth-child(${index + 1})`);
            if (th) {
                th.style.display = hide ? 'none' : '';
            }
            state.rows.forEach((row) => {
                const cell = row.children[index];
                if (cell) {
                    cell.style.display = hide ? 'none' : '';
                }
            });
        });

        if (state.emptyRow) {
            const visibleCount = state.headers.length - hiddenColumns.size;
            const cell = state.emptyRow.querySelector('td');
            if (cell) {
                cell.colSpan = Math.max(1, visibleCount);
            }
        }
    };

    const applyPagination = (state) => {
        const total = state.filteredRows.length;
        const pageSize = state.pageSize > 0 ? state.pageSize : total;
        const pageCount = Math.max(1, Math.ceil(total / pageSize));
        state.pageIndex = Math.min(state.pageIndex, pageCount - 1);
        const start = state.pageIndex * pageSize;
        const end = start + pageSize;

        state.rows.forEach((row) => {
            row.classList.add('hidden');
        });

        state.filteredRows.forEach((row, index) => {
            row.classList.toggle('hidden', index < start || index >= end);
        });

        if (state.emptyRow) {
            state.emptyRow.classList.toggle('hidden', total > 0);
        }

        if (state.pageInfoEl) {
            const shownStart = total === 0 ? 0 : start + 1;
            const shownEnd = Math.min(end, total);
            state.pageInfoEl.textContent = total === 0
                ? 'No results'
                : `Showing ${shownStart}-${shownEnd} of ${total}`;
        }

        if (state.pageTotalEl) {
            state.pageTotalEl.textContent = `of ${pageCount}`;
        }

        if (state.pageInputEl) {
            state.pageInputEl.value = String(Math.min(pageCount, state.pageIndex + 1));
        }

        if (state.pagePrevBtn) {
            state.pagePrevBtn.disabled = state.pageIndex <= 0;
        }
        if (state.pageNextBtn) {
            state.pageNextBtn.disabled = state.pageIndex >= pageCount - 1;
        }
        if (state.pageFirstBtn) {
            state.pageFirstBtn.disabled = state.pageIndex <= 0;
        }
        if (state.pageLastBtn) {
            state.pageLastBtn.disabled = state.pageIndex >= pageCount - 1;
        }
    };

    const applySort = (state) => {
        if (state.sortIndex === null || state.sortIndex === undefined) {
            return;
        }

        const index = state.sortIndex;
        const direction = state.sortDir === 'desc' ? -1 : 1;
        const getValue = (row) => {
            const cell = row.children[index];
            const raw = String(cell ? cell.dataset.sortValue || cell.textContent || '' : '').trim();
            const numeric = Number.parseFloat(raw.replace(/[^0-9.-]/g, ''));
            if (!Number.isNaN(numeric) && raw.match(/[0-9]/)) {
                return numeric;
            }
            return raw.toLowerCase();
        };

        state.filteredRows.sort((a, b) => {
            const aValue = getValue(a);
            const bValue = getValue(b);
            if (aValue < bValue) {
                return -1 * direction;
            }
            if (aValue > bValue) {
                return 1 * direction;
            }
            return 0;
        });

        state.filteredRows.forEach((row) => {
            state.tbody.appendChild(row);
        });

        const headers = Array.from(state.table.querySelectorAll('thead th'));
        headers.forEach((header, headerIndex) => {
            header.classList.toggle('is-sorted', headerIndex === index);
            header.classList.toggle('asc', headerIndex === index && state.sortDir === 'asc');
            header.classList.toggle('desc', headerIndex === index && state.sortDir === 'desc');
        });
    };

    const getTableState = (table) => {
        if (!table) {
            return null;
        }

        if (tableStates.has(table)) {
            return tableStates.get(table);
        }

        const tbody = table.querySelector('tbody');
        if (!tbody) {
            return null;
        }

        const headers = Array.from(table.querySelectorAll('thead th')).map((cell) =>
            String(cell.textContent || '').trim()
        );
        const rows = Array.from(tbody.querySelectorAll('tr')).filter((row) => !row.hasAttribute('data-empty-row'));

        rows.forEach((row) => {
            Array.from(row.children).forEach((cell, index) => {
                if (!cell.dataset.label) {
                    cell.dataset.label = headers[index] || '';
                }
            });
        });

        const state = {
            table,
            tbody,
            headers,
            rows,
            filteredRows: rows.slice(),
            pageIndex: 0,
            pageSize: rows.length,
            hiddenColumns: new Set(),
            emptyRow: null,
            pageInfoEl: null,
            pagePrevBtn: null,
            pageNextBtn: null,
            pageFirstBtn: null,
            pageLastBtn: null,
            pageInputEl: null,
            pageTotalEl: null,
            sortIndex: null,
            sortDir: 'asc',
        };

        tableStates.set(table, state);
        const headerCells = Array.from(table.querySelectorAll('thead th'));
        headerCells.forEach((header, index) => {
            if (header.hasAttribute('data-no-sort')) {
                return;
            }
            header.classList.add('sortable');
            header.addEventListener('click', () => {
                state.sortDir = state.sortIndex === index && state.sortDir === 'asc' ? 'desc' : 'asc';
                state.sortIndex = index;
                applySort(state);
                applyPagination(state);
            });
        });
        applyColumnVisibility(state);
        applyPagination(state);
        return state;
    };

    document.querySelectorAll('[data-table]').forEach((table) => {
        getTableState(table);
    });

    document.querySelectorAll('[data-table-controls]').forEach((controls) => {
        const targetTableId = controls.dataset.targetTable || '';
        const table = targetTableId ? document.getElementById(targetTableId) : null;
        const state = getTableState(table);
        if (!state) {
            return;
        }

        const densityButton = controls.querySelector('[data-table-density]');
        const pageSizeSelect = controls.querySelector('[data-table-page-size]');
        const columnsMenu = controls.querySelector('[data-table-columns-menu]');

        if (densityButton) {
            const densityKey = table.id ? `table-density-${table.id}` : '';
            const savedDensity = densityKey ? storageGet(densityKey) : null;
            if (savedDensity === 'compact') {
                table.classList.add('table--compact');
            }
            densityButton.addEventListener('click', () => {
                table.classList.toggle('table--compact');
                if (densityKey) {
                    storageSet(densityKey, table.classList.contains('table--compact') ? 'compact' : 'comfortable');
                }
            });
        }

        if (pageSizeSelect) {
            const sizeKey = table.id ? `table-page-size-${table.id}` : '';
            const savedSize = sizeKey ? Number(storageGet(sizeKey)) : 0;
            if (savedSize && Array.from(pageSizeSelect.options).some((opt) => Number(opt.value) === savedSize)) {
                pageSizeSelect.value = String(savedSize);
                state.pageSize = savedSize;
            } else {
                state.pageSize = Number(pageSizeSelect.value) || state.pageSize;
            }
            pageSizeSelect.addEventListener('change', () => {
                state.pageSize = Number(pageSizeSelect.value) || state.pageSize;
                state.pageIndex = 0;
                if (sizeKey) {
                    storageSet(sizeKey, String(state.pageSize));
                }
                applyPagination(state);
            });
        }

        if (columnsMenu) {
            const columnsKey = table.id ? `table-hidden-columns-${table.id}` : '';
            const storedHidden = columnsKey ? storageGet(columnsKey) : null;
            if (storedHidden) {
                storedHidden.split(',').forEach((value) => {
                    const index = Number(value);
                    if (Number.isInteger(index)) {
                        state.hiddenColumns.add(index);
                    }
                });
            }

            columnsMenu.innerHTML = '';
            state.headers.forEach((label, index) => {
                const item = document.createElement('label');
                const isHidden = state.hiddenColumns.has(index);
                item.innerHTML = `<input type="checkbox" ${isHidden ? '' : 'checked'} data-column-index="${index}"> <span>${label || `Column ${index + 1}`}</span>`;
                columnsMenu.appendChild(item);
            });

            columnsMenu.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const index = Number(checkbox.dataset.columnIndex);
                    if (!Number.isInteger(index)) {
                        return;
                    }
                    if (checkbox.checked) {
                        state.hiddenColumns.delete(index);
                    } else {
                        state.hiddenColumns.add(index);
                    }
                    if (columnsKey) {
                        storageSet(columnsKey, Array.from(state.hiddenColumns).join(','));
                    }
                    applyColumnVisibility(state);
                });
            });

            applyColumnVisibility(state);
        }
    });

    document.querySelectorAll('[data-table-pagination]').forEach((wrapper) => {
        const targetTableId = wrapper.dataset.targetTable || '';
        const table = targetTableId ? document.getElementById(targetTableId) : null;
        const state = getTableState(table);
        if (!state) {
            return;
        }

        state.pageInfoEl = wrapper.querySelector('[data-table-page-info]');
        state.pagePrevBtn = wrapper.querySelector('[data-table-page-prev]');
        state.pageNextBtn = wrapper.querySelector('[data-table-page-next]');
        state.pageFirstBtn = wrapper.querySelector('[data-table-page-first]');
        state.pageLastBtn = wrapper.querySelector('[data-table-page-last]');
        state.pageInputEl = wrapper.querySelector('[data-table-page-input]');
        state.pageTotalEl = wrapper.querySelector('[data-table-page-total]');

        if (state.pagePrevBtn) {
            state.pagePrevBtn.addEventListener('click', () => {
                state.pageIndex = Math.max(0, state.pageIndex - 1);
                applyPagination(state);
            });
        }

        if (state.pageNextBtn) {
            state.pageNextBtn.addEventListener('click', () => {
                state.pageIndex += 1;
                applyPagination(state);
            });
        }

        if (state.pageFirstBtn) {
            state.pageFirstBtn.addEventListener('click', () => {
                state.pageIndex = 0;
                applyPagination(state);
            });
        }

        if (state.pageLastBtn) {
            state.pageLastBtn.addEventListener('click', () => {
                const total = state.filteredRows.length;
                const pageSize = state.pageSize > 0 ? state.pageSize : total;
                const pageCount = Math.max(1, Math.ceil(total / pageSize));
                state.pageIndex = pageCount - 1;
                applyPagination(state);
            });
        }

        if (state.pageInputEl) {
            state.pageInputEl.addEventListener('change', () => {
                const total = state.filteredRows.length;
                const pageSize = state.pageSize > 0 ? state.pageSize : total;
                const pageCount = Math.max(1, Math.ceil(total / pageSize));
                const nextPage = Math.min(pageCount, Math.max(1, Number(state.pageInputEl.value) || 1));
                state.pageIndex = nextPage - 1;
                applyPagination(state);
            });

            state.pageInputEl.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    state.pageInputEl.dispatchEvent(new Event('change'));
                }
            });
        }

        applyPagination(state);
    });

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
        const state = getTableState(table);
        if (!state || state.rows.length === 0) {
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

        if (!state.emptyRow) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'list-filter-empty-row hidden';
            emptyRow.setAttribute('data-empty-row', 'true');
            emptyRow.innerHTML = `<td colspan="${state.headers.length}">${emptyMessage}</td>`;
            state.tbody.appendChild(emptyRow);
            state.emptyRow = emptyRow;
        }

        const runFilter = () => {
            const searchTerm = normalizeText(searchInput ? searchInput.value : '');
            const selectedFilter = normalizeText(filterSelect ? filterSelect.value : '');
            const fromDate = parseDateValue(fromInput ? fromInput.value : '');
            const toDate = parseDateValue(toInput ? toInput.value : '');

            state.filteredRows = state.rows.filter((row) => {
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

                return searchPass && filterPass && datePass;
            });

            state.pageIndex = 0;
            applySort(state);
            applyPagination(state);
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

    document.querySelectorAll('[data-avatar-input]').forEach((avatarInput) => {
        const scope = avatarInput.closest('[data-avatar-scope]') || document;
        const avatarPreview = scope.querySelector('[data-avatar-preview]');
        if (!avatarPreview) {
            return;
        }

        avatarInput.addEventListener('change', () => {
            const file = avatarInput.files && avatarInput.files[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                avatarPreview.style.backgroundImage = `url(${reader.result})`;
                avatarPreview.textContent = '';
                avatarPreview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('[data-image-input]').forEach((imageInput) => {
        const scope = imageInput.closest('[data-image-scope]') || document;
        const imagePreview = scope.querySelector('[data-image-preview]');
        if (!imagePreview) {
            return;
        }

        imageInput.addEventListener('change', () => {
            const file = imageInput.files && imageInput.files[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                imagePreview.style.backgroundImage = `url(${reader.result})`;
                imagePreview.textContent = '';
                imagePreview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('[data-chip-input]').forEach((input) => {
        const previewId = input.dataset.chipPreview || '';
        const preview = previewId ? document.getElementById(previewId) : null;
        if (!preview) {
            return;
        }

        const updatePreview = () => {
            preview.innerHTML = '';
            const values = String(input.value || '')
                .split(',')
                .map((value) => value.trim())
                .filter((value) => value.length > 0);

            values.forEach((value) => {
                const chip = document.createElement('span');
                chip.className = 'chip';
                chip.textContent = value;
                preview.appendChild(chip);
            });
        };

        input.addEventListener('input', updatePreview);
        updatePreview();
    });
});
