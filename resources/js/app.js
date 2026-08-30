import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-fm-sidebar]');
    const sidebarBackdrop = document.querySelector(
        '[data-fm-sidebar-backdrop]'
    );

    const sidebarToggles = document.querySelectorAll(
        '[data-fm-sidebar-toggle]'
    );

    const setSidebarState = (isOpen) => {
        if (!sidebar || !sidebarBackdrop) {
            return;
        }

        sidebar.classList.toggle('is-open', isOpen);
        sidebarBackdrop.classList.toggle('is-visible', isOpen);

        document.body.classList.toggle(
            'fm-sidebar-open',
            isOpen
        );
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

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            setSidebarState(false);
        }
    });

    document
        .querySelectorAll('[data-password-toggle]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                const input = document.getElementById(targetId);

                if (!input) {
                    return;
                }

                const showPassword = input.type === 'password';

                input.type = showPassword
                    ? 'text'
                    : 'password';

                const icon = button.querySelector('i');

                if (icon) {
                    icon.classList.toggle(
                        'bi-eye',
                        !showPassword
                    );

                    icon.classList.toggle(
                        'bi-eye-slash',
                        showPassword
                    );
                }

                button.setAttribute(
                    'aria-label',
                    showPassword
                        ? 'Hide password'
                        : 'Show password'
                );
            });
        });

    document
        .querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach((element) => {
            new bootstrap.Tooltip(element);
        });
});
document.addEventListener('DOMContentLoaded', () => {
    document
        .querySelectorAll('[data-company-contact-select]')
        .forEach((contactSelect) => {
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
});
