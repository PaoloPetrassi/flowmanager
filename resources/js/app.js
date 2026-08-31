import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-fm-sidebar]');
    const sidebarBackdrop = document.querySelector('[data-fm-sidebar-backdrop]');
    const sidebarToggles = document.querySelectorAll('[data-fm-sidebar-toggle]');

    const setSidebarState = (isOpen) => {
        if (!sidebar || !sidebarBackdrop) {
            return;
        }

        sidebar.classList.toggle('is-open', isOpen);
        sidebarBackdrop.classList.toggle('is-visible', isOpen);
        document.body.classList.toggle('fm-sidebar-open', isOpen);

        sidebarToggles.forEach((toggle) => {
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    };

    sidebarToggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const isOpen = sidebar?.classList.contains('is-open');
            setSidebarState(!isOpen);
        });
    });

    sidebarBackdrop?.addEventListener('click', () => {
        setSidebarState(false);
    });

    sidebar?.querySelectorAll('a.fm-nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                setSidebarState(false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) {
            setSidebarState(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const isEditable = target instanceof HTMLInputElement
            || target instanceof HTMLTextAreaElement
            || target instanceof HTMLSelectElement
            || target?.isContentEditable;

        if (event.key === '/' && !isEditable) {
            const searchInput = document.querySelector('.fm-header-search input[name="q"]');

            if (searchInput && window.innerWidth >= 992) {
                event.preventDefault();
                searchInput.focus();
            }
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            setSidebarState(false);
        }
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;
            const input = document.getElementById(targetId);

            if (!input) {
                return;
            }

            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';

            const icon = button.querySelector('i');

            if (icon) {
                icon.classList.toggle('bi-eye', !showPassword);
                icon.classList.toggle('bi-eye-slash', showPassword);
            }

            button.setAttribute(
                'aria-label',
                showPassword
                    ? button.dataset.labelHide || 'Hide password'
                    : button.dataset.labelShow || 'Show password'
            );
        });
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        new bootstrap.Tooltip(element);
    });

    document.querySelectorAll('[data-confirm]').forEach((control) => {
        control.addEventListener('click', (event) => {
            const message = control.dataset.confirm;

            if (message && !window.confirm(message)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });
    });

    document.querySelectorAll('[data-company-contact-select]').forEach((contactSelect) => {
        const companySourceId = contactSelect.dataset.companySource;
        const companySelect = document.getElementById(companySourceId);

        if (!companySelect) {
            return;
        }

        const filterContacts = () => {
            const companyId = companySelect.value;
            const currentOption = contactSelect.selectedOptions[0];

            Array.from(contactSelect.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const optionCompanyId = option.dataset.companyId || '';
                const isVisible = !companyId
                    || !optionCompanyId
                    || optionCompanyId === companyId;

                option.hidden = !isVisible;
                option.disabled = !isVisible;
            });

            if (currentOption?.disabled) {
                contactSelect.value = '';
            }
        };

        companySelect.addEventListener('change', filterContacts);
        filterContacts();
    });

    document.querySelectorAll('[data-project-filtered-select]').forEach((filteredSelect) => {
        const projectSourceId = filteredSelect.dataset.projectSource;
        const projectSelect = document.getElementById(projectSourceId);

        if (!projectSelect) {
            return;
        }

        const filterOptions = () => {
            const projectId = projectSelect.value;

            Array.from(filteredSelect.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const optionProjectId = option.dataset.projectId || '';
                const isVisible = !projectId || optionProjectId === projectId;

                option.hidden = !isVisible;
                option.disabled = !isVisible;

                if (!isVisible && option.selected) {
                    option.selected = false;
                }
            });
        };

        projectSelect.addEventListener('change', filterOptions);
        filterOptions();
    });

    document.querySelectorAll('[data-recurrence-select]').forEach((recurrenceSelect) => {
        const form = recurrenceSelect.closest('form');
        const intervalInput = form?.querySelector('#recurrence_interval');
        const endInput = form?.querySelector('#recurrence_ends_at');

        const updateRecurrenceControls = () => {
            const isRecurring = recurrenceSelect.value !== 'none';

            [intervalInput, endInput].forEach((input) => {
                if (!input) {
                    return;
                }

                input.disabled = !isRecurring;
                input.closest('.col-6, .col-md-4')?.classList.toggle('opacity-50', !isRecurring);
            });
        };

        recurrenceSelect.addEventListener('change', updateRecurrenceControls);
        updateRecurrenceControls();
    });

    const kanbanBoard = document.querySelector('[data-kanban-board]');

    if (kanbanBoard?.dataset.kanbanEnabled === '1') {
        let draggedCard = null;

        const updateCounts = () => {
            kanbanBoard.querySelectorAll('[data-kanban-status]').forEach((column) => {
                const count = column.querySelectorAll('[data-kanban-card]').length;
                const badge = column.querySelector('[data-kanban-count]');

                if (badge) {
                    badge.textContent = String(count);
                }
            });
        };

        const updateCardStatus = async (card, status, destination) => {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!token || !card.dataset.updateUrl) {
                return;
            }

            const response = await fetch(card.dataset.updateUrl, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ status }),
            });

            if (!response.ok) {
                throw new Error('Unable to update status.');
            }

            destination.querySelector('[data-kanban-list]')?.append(card);

            const select = card.querySelector('select[name="status"]');
            if (select) {
                select.value = status;
            }

            updateCounts();
        };

        kanbanBoard.querySelectorAll('[data-kanban-card]').forEach((card) => {
            card.addEventListener('dragstart', () => {
                draggedCard = card;
                card.classList.add('is-dragging');
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('is-dragging');
                draggedCard = null;
                kanbanBoard.querySelectorAll('.is-drop-target').forEach((column) => {
                    column.classList.remove('is-drop-target');
                });
            });
        });

        kanbanBoard.querySelectorAll('[data-kanban-status]').forEach((column) => {
            column.addEventListener('dragover', (event) => {
                if (!draggedCard) {
                    return;
                }

                event.preventDefault();
                column.classList.add('is-drop-target');
            });

            column.addEventListener('dragleave', () => {
                column.classList.remove('is-drop-target');
            });

            column.addEventListener('drop', async (event) => {
                event.preventDefault();
                column.classList.remove('is-drop-target');

                if (!draggedCard) {
                    return;
                }

                const status = column.dataset.kanbanStatus;
                const currentColumn = draggedCard.closest('[data-kanban-status]');

                if (!status || currentColumn === column) {
                    return;
                }

                try {
                    await updateCardStatus(draggedCard, status, column);
                } catch (error) {
                    window.alert(error.message || 'Unable to update status.');
                }
            });
        });
    }
});

    // v0.11 command palette
    const commandPalette = document.querySelector('[data-command-palette]');
    const commandInput = document.querySelector('[data-command-input]');
    const commandResults = document.querySelector('[data-command-results]');
    let commandTimer = null;

    const openCommandPalette = () => {
        if (!commandPalette || !commandInput) return;
        commandPalette.hidden = false;
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => commandInput.focus());
    };
    const closeCommandPalette = () => {
        if (!commandPalette) return;
        commandPalette.hidden = true;
        document.body.style.overflow = '';
        if (commandInput) commandInput.value = '';
    };
    document.querySelectorAll('[data-command-palette-open]').forEach((button) => button.addEventListener('click', openCommandPalette));
    commandPalette?.addEventListener('click', (event) => { if (event.target === commandPalette) closeCommandPalette(); });
    document.addEventListener('keydown', (event) => {
        const editable = event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement || event.target instanceof HTMLSelectElement || event.target?.isContentEditable;
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); openCommandPalette(); }
        if (event.key === 'Escape' && commandPalette && !commandPalette.hidden) closeCommandPalette();
        if (!editable && event.key.toLowerCase() === 'k' && !event.ctrlKey && !event.metaKey) { /* reserved */ }
    });
    commandInput?.addEventListener('input', () => {
        clearTimeout(commandTimer);
        const q = commandInput.value.trim();
        if (q.length < 2) return;
        commandTimer = setTimeout(async () => {
            try {
                const response = await fetch(`/command-palette?q=${encodeURIComponent(q)}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();
                if (!commandResults) return;
                commandResults.innerHTML = (data.results || []).length
                    ? data.results.map((item) => `<a class="fm-command-item" href="${item.url}"><span><i class="bi bi-arrow-return-right"></i>${escapeHtml(item.label)}</span><small>${escapeHtml(item.type)}</small></a>`).join('')
                    : '<div class="p-4 text-center text-secondary">No matching records</div>';
            } catch (_) {}
        }, 180);
    });
    function escapeHtml(value) {
        const div = document.createElement('div'); div.textContent = value ?? ''; return div.innerHTML;
    }

    // v0.10 bulk selection
    document.querySelectorAll('[data-bulk-container]').forEach((container) => {
        const toolbar = container.querySelector('[data-bulk-toolbar]');
        const count = container.querySelector('[data-bulk-count]');
        const form = container.querySelector('[data-bulk-form]');
        const all = container.querySelector('[data-bulk-select-all]');
        const boxes = () => Array.from(container.querySelectorAll('[data-bulk-checkbox]'));
        const refresh = () => {
            const selected = boxes().filter((box) => box.checked);
            toolbar?.classList.toggle('is-visible', selected.length > 0);
            if (count) count.textContent = String(selected.length);
        };
        all?.addEventListener('change', () => { boxes().forEach((box) => { box.checked = all.checked; }); refresh(); });
        boxes().forEach((box) => box.addEventListener('change', refresh));
        form?.addEventListener('submit', () => {
            form.querySelectorAll('input[data-generated-id]').forEach((node) => node.remove());
            boxes().filter((box) => box.checked).forEach((box) => {
                const input = document.createElement('input'); input.type='hidden'; input.name='ids[]'; input.value=box.value; input.dataset.generatedId='1'; form.appendChild(input);
            });
        });
    });

    // v0.11 persisted table column preferences
    const tablePreferenceNode = document.getElementById('fm-table-preferences');
    if (tablePreferenceNode) {
        try {
            const tablePreferences = JSON.parse(tablePreferenceNode.textContent || '{}');
            document.querySelectorAll('[data-table-resource]').forEach((table) => {
                const visible = tablePreferences[table.dataset.tableResource];
                if (!Array.isArray(visible)) return;
                table.querySelectorAll('[data-column]').forEach((cell) => {
                    cell.hidden = !visible.includes(cell.dataset.column);
                });
            });
        } catch (_) {}
    }
