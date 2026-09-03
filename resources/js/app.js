import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

const initializeFlowManager = () => {
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
            setSidebarState(!sidebar?.classList.contains('is-open'));
        });
    });

    sidebarBackdrop?.addEventListener('click', () => setSidebarState(false));

    sidebar?.querySelectorAll('a.fm-nav-link').forEach((link) => {
        if (link.classList.contains('active')) {
            link.setAttribute('aria-current', 'page');
        }

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

    const commandPalette = document.querySelector('[data-command-palette]');
    const commandDialog = commandPalette?.querySelector('.fm-command-dialog');
    const commandInput = document.querySelector('[data-command-input]');
    const commandResults = document.querySelector('[data-command-results]');
    const defaultCommandMarkup = commandResults?.innerHTML ?? '';
    let commandTimer = null;
    let commandActiveIndex = -1;
    let commandOpener = null;

    const commandItems = () => Array.from(commandResults?.querySelectorAll('.fm-command-item') ?? []);

    const updateCommandSelection = (index) => {
        const items = commandItems();

        items.forEach((item, itemIndex) => {
            const isActive = itemIndex === index;
            item.classList.toggle('is-active', isActive);
            item.setAttribute('aria-selected', isActive ? 'true' : 'false');

            if (!item.id) {
                item.id = `fm-command-option-${itemIndex}`;
            }
        });

        commandActiveIndex = items.length === 0 ? -1 : Math.max(0, Math.min(index, items.length - 1));
        const activeItem = items[commandActiveIndex];

        commandInput?.setAttribute('aria-activedescendant', activeItem?.id ?? '');
        activeItem?.scrollIntoView({ block: 'nearest' });
    };

    const resetCommandResults = () => {
        if (!commandResults) {
            return;
        }

        commandResults.innerHTML = defaultCommandMarkup;
        updateCommandSelection(commandItems().length > 0 ? 0 : -1);
    };

    const openCommandPalette = (opener = null) => {
        if (!commandPalette || !commandInput) {
            return;
        }

        commandOpener = opener instanceof HTMLElement ? opener : document.activeElement;
        commandPalette.hidden = false;
        document.body.classList.add('fm-modal-open');
        commandInput.setAttribute('aria-expanded', 'true');
        resetCommandResults();

        requestAnimationFrame(() => commandInput.focus());
    };

    const closeCommandPalette = () => {
        if (!commandPalette || commandPalette.hidden) {
            return;
        }

        commandPalette.hidden = true;
        document.body.classList.remove('fm-modal-open');
        commandInput?.setAttribute('aria-expanded', 'false');

        if (commandInput) {
            commandInput.value = '';
            commandInput.removeAttribute('aria-activedescendant');
        }

        resetCommandResults();

        if (commandOpener instanceof HTMLElement && document.contains(commandOpener)) {
            commandOpener.focus();
        }

        commandOpener = null;
    };

    document.querySelectorAll('[data-command-palette-open]').forEach((button) => {
        button.addEventListener('click', () => openCommandPalette(button));
    });

    commandPalette?.addEventListener('click', (event) => {
        if (event.target === commandPalette) {
            closeCommandPalette();
        }
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const editable = target instanceof HTMLInputElement
            || target instanceof HTMLTextAreaElement
            || target instanceof HTMLSelectElement
            || target?.isContentEditable;

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openCommandPalette();
            return;
        }

        if (event.key === 'Escape' && commandPalette && !commandPalette.hidden) {
            event.preventDefault();
            closeCommandPalette();
            return;
        }

        if (!editable && event.key.toLowerCase() === 'k' && !event.ctrlKey && !event.metaKey) {
            // Reserved for a future single-key command shortcut.
        }
    });

    commandDialog?.addEventListener('keydown', (event) => {
        if (event.key === 'Tab') {
            const focusable = Array.from(commandDialog.querySelectorAll('input, a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'))
                .filter((element) => !element.hidden && element.getClientRects().length > 0);

            if (focusable.length === 0) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    commandInput?.addEventListener('keydown', (event) => {
        const items = commandItems();

        if (items.length === 0) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            updateCommandSelection((commandActiveIndex + 1) % items.length);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            updateCommandSelection((commandActiveIndex - 1 + items.length) % items.length);
        } else if (event.key === 'Home') {
            event.preventDefault();
            updateCommandSelection(0);
        } else if (event.key === 'End') {
            event.preventDefault();
            updateCommandSelection(items.length - 1);
        } else if (event.key === 'Enter' && commandActiveIndex >= 0) {
            event.preventDefault();
            items[commandActiveIndex]?.click();
        }
    });

    commandInput?.addEventListener('input', () => {
        clearTimeout(commandTimer);

        const query = commandInput.value.trim();

        if (query.length < 2) {
            resetCommandResults();
            return;
        }

        commandTimer = window.setTimeout(async () => {
            try {
                const response = await fetch(`/command-palette?q=${encodeURIComponent(query)}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok || !commandResults) {
                    return;
                }

                const data = await response.json();
                const results = data.results || [];

                commandResults.innerHTML = results.length > 0
                    ? results.map((item) => `
                        <a class="fm-command-item" role="option" href="${escapeHtml(item.url)}">
                            <span class="fm-command-main">
                                <i class="bi ${escapeHtml(item.icon || 'bi-arrow-return-right')}" aria-hidden="true"></i>
                                <span class="min-w-0">
                                    <strong>${escapeHtml(item.label)}</strong>
                                    ${item.meta ? `<small class="fm-command-meta">${escapeHtml(item.meta)}</small>` : ''}
                                </span>
                            </span>
                            <small>${escapeHtml(item.type)}</small>
                        </a>
                    `).join('')
                    : `<div class="p-4 text-center text-secondary" role="status">${escapeHtml(commandResults.dataset.emptyMessage || 'No matching records were found.')}</div>`;

                updateCommandSelection(results.length > 0 ? 0 : -1);
            } catch (_) {
                // Keep the existing results when the request cannot be completed.
            }
        }, 180);
    });

    document.querySelectorAll('[data-bulk-container]').forEach((container) => {
        const toolbar = container.querySelector('[data-bulk-toolbar]');
        const count = container.querySelector('[data-bulk-count]');
        const form = container.querySelector('[data-bulk-form]');
        const selectAll = container.querySelector('[data-bulk-select-all]');
        const actionSelect = form?.querySelector('[data-bulk-action]');
        const valueSelect = form?.querySelector('[data-bulk-value]');
        const checkboxes = () => Array.from(container.querySelectorAll('[data-bulk-checkbox]'));

        const syncBulkActionControls = () => {
            if (!actionSelect || !valueSelect) {
                return;
            }

            const action = actionSelect.value;
            const needsValue = action !== 'delete';

            valueSelect.disabled = !needsValue;
            valueSelect.hidden = !needsValue;

            Array.from(valueSelect.querySelectorAll('optgroup')).forEach((group) => {
                const enabled = group.dataset.bulkValueGroup === action;
                group.disabled = !enabled;
                group.hidden = !enabled;
            });

            if (needsValue) {
                const selectedOption = valueSelect.selectedOptions[0];
                const selectedGroup = selectedOption?.parentElement;

                if (selectedGroup instanceof HTMLOptGroupElement && selectedGroup.disabled) {
                    valueSelect.value = '';
                }
            } else {
                valueSelect.value = '';
            }
        };

        const refreshBulkSelection = () => {
            const boxes = checkboxes();
            const selected = boxes.filter((box) => box.checked);

            toolbar?.classList.toggle('is-visible', selected.length > 0);

            if (count) {
                count.textContent = String(selected.length);
            }

            if (selectAll) {
                selectAll.checked = boxes.length > 0 && selected.length === boxes.length;
                selectAll.indeterminate = selected.length > 0 && selected.length < boxes.length;
            }
        };

        selectAll?.addEventListener('change', () => {
            checkboxes().forEach((box) => {
                box.checked = selectAll.checked;
            });
            refreshBulkSelection();
        });

        checkboxes().forEach((box) => box.addEventListener('change', refreshBulkSelection));
        actionSelect?.addEventListener('change', syncBulkActionControls);

        form?.addEventListener('submit', (event) => {
            if (actionSelect?.value === 'delete' && !window.confirm(form.dataset.deleteConfirm || 'Delete the selected records?')) {
                event.preventDefault();
                return;
            }
            form.querySelectorAll('input[data-generated-id]').forEach((node) => node.remove());

            checkboxes().filter((box) => box.checked).forEach((box) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = box.value;
                input.dataset.generatedId = '1';
                form.appendChild(input);
            });
        });

        syncBulkActionControls();
        refreshBulkSelection();
    });

    document.querySelectorAll('[data-dashboard-period]').forEach((periodSelect) => {
        const form = periodSelect.closest('form');
        const customDates = Array.from(form?.querySelectorAll('[data-dashboard-custom-date]') || []);

        const syncDashboardPeriod = () => {
            const isCustom = periodSelect.value === 'custom';

            customDates.forEach((input) => {
                input.disabled = !isCustom;
                input.required = isCustom;
                input.closest('[data-dashboard-custom-field]')?.classList.toggle('opacity-50', !isCustom);
            });
        };

        periodSelect.addEventListener('change', syncDashboardPeriod);
        syncDashboardPeriod();
    });

    document.querySelectorAll('[data-automation-form]').forEach((form) => {
        const actionSelect = form.querySelector('[data-automation-action]');
        const userField = form.querySelector('[data-automation-user-field]');
        const priorityField = form.querySelector('[data-automation-priority-field]');
        const userSelect = userField?.querySelector('select');
        const prioritySelect = priorityField?.querySelector('select');

        const syncAutomationFields = () => {
            if (!actionSelect) {
                return;
            }

            const needsUser = ['notify_user', 'assign_user'].includes(actionSelect.value);
            const needsPriority = ['set_ticket_priority', 'set_task_priority'].includes(actionSelect.value);

            if (userField) {
                userField.hidden = !needsUser;
            }

            if (userSelect) {
                userSelect.disabled = !needsUser;
                userSelect.required = needsUser;
            }

            if (priorityField) {
                priorityField.hidden = !needsPriority;
            }

            if (prioritySelect) {
                prioritySelect.disabled = !needsPriority;
                prioritySelect.required = needsPriority;
            }
        };

        actionSelect?.addEventListener('change', syncAutomationFields);
        syncAutomationFields();
    });

    const tablePreferenceNode = document.getElementById('fm-table-preferences');

    if (tablePreferenceNode) {
        try {
            const tablePreferences = JSON.parse(tablePreferenceNode.textContent || '{}');

            document.querySelectorAll('[data-table-resource]').forEach((table) => {
                const visible = tablePreferences[table.dataset.tableResource];

                if (!Array.isArray(visible)) {
                    return;
                }

                table.querySelectorAll('[data-column]').forEach((cell) => {
                    cell.hidden = !visible.includes(cell.dataset.column);
                });
            });
        } catch (_) {
            // Ignore malformed preference data and preserve the default table layout.
        }
    }
};

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';

    return div.innerHTML;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFlowManager, { once: true });
} else {
    initializeFlowManager();
}
