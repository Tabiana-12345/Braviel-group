document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-print-users]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });

    const permissionForm = document.querySelector('.role-permissions-form');
    if (permissionForm) {
        const permissionInputs = [...permissionForm.querySelectorAll('[data-permission]')];
        const selectAllInputs = [...permissionForm.querySelectorAll('[data-select-all]')];

        const syncSelectAll = () => {
            selectAllInputs.forEach((selectAll) => {
                const permissions = selectAll.dataset.selectAll === 'all'
                    ? permissionInputs
                    : [...permissionForm.querySelectorAll(`[data-permission-row="${selectAll.dataset.selectAll}"] [data-permission]`)];
                const selectedCount = permissions.filter((permission) => permission.checked).length;

                selectAll.checked = permissions.length > 0 && selectedCount === permissions.length;
                selectAll.indeterminate = selectedCount > 0 && selectedCount < permissions.length;
            });
        };

        permissionForm.addEventListener('change', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLInputElement)) {
                return;
            }

            if (target.matches('[data-select-all]')) {
                const permissions = target.dataset.selectAll === 'all'
                    ? permissionInputs
                    : [...permissionForm.querySelectorAll(`[data-permission-row="${target.dataset.selectAll}"] [data-permission]`)];

                permissions.forEach((permission) => {
                    permission.checked = target.checked;
                });
            }

            syncSelectAll();
        });

        syncSelectAll();
    }

    const sidebar = document.getElementById('admin-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const closeButton = document.querySelector('[data-sidebar-close]');

    if (!sidebar || !toggle || !closeButton) {
        return;
    }

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        document.body.classList.toggle('sidebar-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        closeButton.hidden = !open;
    };

    toggle.addEventListener('click', () => {
        setOpen(!sidebar.classList.contains('is-open'));
    });

    closeButton.addEventListener('click', () => setOpen(false));

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
            setOpen(false);
            toggle.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            setOpen(false);
        }
    });
});
