(() => {
    const STORAGE_KEY = 'wrplumb.hr.sidebarCollapsed';
    const GROUP_KEY = 'wrplumb.hr.openSidebarGroup';
    const body = document.body;
    const sidebarToggle = document.getElementById('hrSidebarToggle');
    const sidebarBackdrop = document.getElementById('hrSidebarBackdrop');
    const sidebar = document.getElementById('hrSidebar');
    const searchInput = document.getElementById('hrGlobalSearch');
    const groupToggles = document.querySelectorAll('[data-sidebar-group-toggle]');
    const mobileBreakpoint = 992;
    const isMobile = () => window.innerWidth < mobileBreakpoint;
    function applySavedSidebarState() {
        if (isMobile()) {
            body.classList.remove('hr-sidebar-collapsed');
            return;
        }
        const saved = localStorage.getItem(STORAGE_KEY);
        const collapsed = saved === null ? true : saved === '1';
        body.classList.toggle('hr-sidebar-collapsed', collapsed);
        sidebarToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }
    function closeMobileSidebar() {
        body.classList.remove('hr-sidebar-open');
        if (isMobile()) {
            sidebarToggle?.setAttribute('aria-expanded', 'false');
        }
    }
    function toggleSidebar() {
        if (isMobile()) {
            const open = body.classList.toggle('hr-sidebar-open');
            sidebarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
            return;
        }
        body.classList.toggle('hr-sidebar-collapsed');
        const collapsed = body.classList.contains('hr-sidebar-collapsed');
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        sidebarToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }
    function closeAllGroups(except = null) {
        document.querySelectorAll('[data-sidebar-group]').forEach((group) => {
            if (group === except) return;
            group.classList.remove('is-open');
            group.querySelector('[data-sidebar-group-toggle]')
                ?.setAttribute('aria-expanded', 'false');
        });
    }
    function openGroup(group, persist = true) {
        if (!group) return;
        closeAllGroups(group);
        group.classList.add('is-open');
        group.querySelector('[data-sidebar-group-toggle]')
            ?.setAttribute('aria-expanded', 'true');
        if (persist) {
            localStorage.setItem(GROUP_KEY, group.dataset.sidebarGroup || '');
        }
    }
    function closeGroup(group) {
        if (!group) return;
        group.classList.remove('is-open');
        group.querySelector('[data-sidebar-group-toggle]')
            ?.setAttribute('aria-expanded', 'false');
        if (localStorage.getItem(GROUP_KEY) === group.dataset.sidebarGroup) {
            localStorage.removeItem(GROUP_KEY);
        }
    }
    function applySavedGroupState() {
        const activeGroup = sidebar?.querySelector('[data-sidebar-group].is-active');
        if (activeGroup) {
            openGroup(activeGroup, false);
            return;
        }
        const savedGroup = localStorage.getItem(GROUP_KEY);
        if (!savedGroup) return;
        const group = sidebar?.querySelector(
            `[data-sidebar-group="${CSS.escape(savedGroup)}"]`
        );
        if (group) {
            openGroup(group, false);
        }
    }
    function handleGroupToggle(toggle) {
        const group = toggle.closest('[data-sidebar-group]');
        if (!group) return;
        if (!isMobile() && body.classList.contains('hr-sidebar-collapsed')) {
            body.classList.remove('hr-sidebar-collapsed');
            localStorage.setItem(STORAGE_KEY, '0');
            sidebarToggle?.setAttribute('aria-expanded', 'true');
            openGroup(group);
            return;
        }
        if (group.classList.contains('is-open')) {
            closeGroup(group);
            return;
        }
        openGroup(group);
    }
    function filterCurrentPage(query) {
        const normalized = query.trim().toLowerCase();
        document.querySelectorAll('[data-hr-search]').forEach((element) => {
            const haystack = (
                element.dataset.hrSearch ||
                element.textContent ||
                ''
            ).toLowerCase();
            element.classList.toggle(
                'is-hr-search-hidden',
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
    applySavedGroupState();
    sidebarToggle?.addEventListener('click', toggleSidebar);
    sidebarBackdrop?.addEventListener('click', closeMobileSidebar);
    groupToggles.forEach((toggle) => {
        toggle.addEventListener('click', () => handleGroupToggle(toggle));
    });
    document.querySelectorAll(
        '#hrSidebar .sidebar-link, #hrSidebar .sidebar-sublink'
    ).forEach((link) => {
        link.addEventListener('click', closeMobileSidebar);
    });
    searchInput?.addEventListener('input', function () {
        filterCurrentPage(this.value);
    });
    document.querySelectorAll('[data-toast-close]').forEach((button) => {
        button.addEventListener('click', () => {
            hideToast(button.closest('.hr-toast'));
        });
    });
    document.querySelectorAll('.hr-toast').forEach((toast) => {
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
        applySavedGroupState();
    });
})();
