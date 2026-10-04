(() => {
    const STORAGE_KEY = 'wrplumb.admin.sidebarCollapsed';
    const body = document.body;
    const sidebarToggle = document.getElementById('adminSidebarToggle');
    const sidebarBackdrop = document.getElementById('adminSidebarBackdrop');
    const searchInput = document.getElementById('adminGlobalSearch');
    const mobileBreakpoint = 992;

    const isMobile = () => window.innerWidth < mobileBreakpoint;

    function applySavedSidebarState() {
        if (isMobile()) {
            body.classList.remove('admin-sidebar-collapsed');
            return;
        }

        const saved = localStorage.getItem(STORAGE_KEY);
        const collapsed = saved === null ? true : saved === '1';

        body.classList.toggle('admin-sidebar-collapsed', collapsed);
        sidebarToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function closeMobileSidebar() {
        body.classList.remove('admin-sidebar-open');

        if (isMobile()) {
            sidebarToggle?.setAttribute('aria-expanded', 'false');
        }
    }

    function toggleSidebar() {
        if (isMobile()) {
            const open = body.classList.toggle('admin-sidebar-open');
            sidebarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
            return;
        }

        body.classList.toggle('admin-sidebar-collapsed');

        const collapsed = body.classList.contains('admin-sidebar-collapsed');
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        sidebarToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function filterCurrentPage(query) {
        const normalized = query.trim().toLowerCase();

        document.querySelectorAll('[data-admin-search]').forEach((element) => {
            const haystack = (
                element.dataset.adminSearch ||
                element.textContent ||
                ''
            ).toLowerCase();

            element.classList.toggle(
                'is-admin-search-hidden',
                normalized !== '' && !haystack.includes(normalized)
            );
        });
    }

    function hideToast(toast) {
        if (!toast || toast.classList.contains('is-hiding')) return;

        toast.classList.add('is-hiding');

        window.setTimeout(() => {
            toast.remove();
        }, 220);
    }

    applySavedSidebarState();

    sidebarToggle?.addEventListener('click', toggleSidebar);
    sidebarBackdrop?.addEventListener('click', closeMobileSidebar);

    document.querySelectorAll('#adminSidebar .sidebar-link').forEach((link) => {
        link.addEventListener('click', closeMobileSidebar);
    });

    searchInput?.addEventListener('input', function () {
        filterCurrentPage(this.value);
    });

    document.querySelectorAll('[data-toast-close]').forEach((button) => {
        button.addEventListener('click', () => {
            hideToast(button.closest('.wr-toast'));
        });
    });

    document.querySelectorAll('.wr-toast').forEach((toast) => {
        window.setTimeout(() => hideToast(toast), 3800);
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            if (!searchInput) return;

            event.preventDefault();
            searchInput.focus();
            searchInput.select();
        }

        if (event.key === 'Escape') {
            closeMobileSidebar();

            if (document.activeElement === searchInput) {
                searchInput.value = '';
                filterCurrentPage('');
                searchInput.blur();
            }
        }
    });

    window.addEventListener('resize', () => {
        if (!isMobile()) {
            closeMobileSidebar();
        }

        applySavedSidebarState();
    });
})();
