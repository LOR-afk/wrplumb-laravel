(() => {
    const STORAGE_KEY = 'wrplumb.inspector.sidebarCollapsed';
    const body = document.body;
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const backdrop = document.getElementById('sidebarBackdrop');
    const profileMenu = document.getElementById('inspectorProfileMenu');
    const profileMenuTrigger = document.getElementById('profileMenuTrigger');
    const searchInput = document.getElementById('inspectorGlobalSearch');
    const mobileBreakpoint = 992;

    const isMobile = () => window.innerWidth < mobileBreakpoint;

    function applySavedSidebarState() {
        if (isMobile()) {
            body.classList.remove('sidebar-collapsed');
            return;
        }

        const saved = localStorage.getItem(STORAGE_KEY);

        // Match the reference UI: start compact unless the user explicitly expanded it.
        const collapsed = saved === null ? true : saved === '1';

        body.classList.toggle('sidebar-collapsed', collapsed);
        sidebarToggleBtn?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function closeMobileSidebar() {
        body.classList.remove('sidebar-mobile-open');
        sidebarToggleBtn?.setAttribute('aria-expanded', 'false');
    }

    function closeProfileMenu() {
        if (!profileMenu || !profileMenuTrigger) return;

        profileMenu.classList.remove('is-open');
        profileMenuTrigger.setAttribute('aria-expanded', 'false');
    }

    function toggleSidebar() {
        if (isMobile()) {
            const open = body.classList.toggle('sidebar-mobile-open');
            sidebarToggleBtn?.setAttribute('aria-expanded', open ? 'true' : 'false');
            return;
        }

        body.classList.toggle('sidebar-collapsed');

        const collapsed = body.classList.contains('sidebar-collapsed');

        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        sidebarToggleBtn?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function filterCurrentPage(query) {
        const normalized = query.trim().toLowerCase();
        const searchable = document.querySelectorAll('[data-inspector-search]');

        searchable.forEach((element) => {
            const haystack = (
                element.dataset.inspectorSearch ||
                element.textContent ||
                ''
            ).toLowerCase();

            element.classList.toggle(
                'is-search-hidden',
                normalized !== '' && !haystack.includes(normalized)
            );
        });
    }

    applySavedSidebarState();

    sidebarToggleBtn?.addEventListener('click', toggleSidebar);
    backdrop?.addEventListener('click', closeMobileSidebar);

    profileMenuTrigger?.addEventListener('click', (event) => {
        event.stopPropagation();

        const open = profileMenu.classList.toggle('is-open');
        profileMenuTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', (event) => {
        if (!profileMenu?.contains(event.target)) {
            closeProfileMenu();
        }
    });

    document.querySelectorAll(
        '#inspectorProfileMenu [data-bs-toggle="modal"]'
    ).forEach((button) => {
        button.addEventListener('click', closeProfileMenu);
    });

    searchInput?.addEventListener('input', function () {
        filterCurrentPage(this.value);
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchInput?.focus();
            searchInput?.select();
        }

        if (event.key === 'Escape') {
            closeMobileSidebar();
            closeProfileMenu();

            if (document.activeElement === searchInput) {
                searchInput.value = '';
                filterCurrentPage('');
                searchInput.blur();
            }
        }
    });

    const inspectorPhotoInput = document.getElementById('inspectorProfilePhoto');
    const inspectorPhotoPreview = document.getElementById('inspectorSettingsPhotoPreview');
    const inspectorRemovePhotoInput = document.getElementById('inspectorRemoveProfilePhoto');
    const inspectorRemovePhotoBtn = document.getElementById('inspectorRemovePhotoBtn');
    const inspectorPhotoStatus = document.getElementById('inspectorPhotoStatus');
    const inspectorFallbackInitials =
        inspectorPhotoPreview?.dataset.initials || 'I';

    function showInspectorInitials() {
        if (!inspectorPhotoPreview) return;

        inspectorPhotoPreview.innerHTML =
            '<span id="inspectorSettingsPhotoInitials">' +
            inspectorFallbackInitials +
            '</span>';
    }

    inspectorPhotoInput?.addEventListener('change', function () {
        const file = this.files?.[0];

        if (!file || !inspectorPhotoPreview) return;

        const reader = new FileReader();

        reader.addEventListener('load', function () {
            inspectorPhotoPreview.innerHTML =
                '<img src="' + reader.result + '" alt="Profile photo preview">';
        });

        reader.readAsDataURL(file);

        if (inspectorRemovePhotoInput) {
            inspectorRemovePhotoInput.value = '0';
        }

        if (inspectorPhotoStatus) {
            inspectorPhotoStatus.textContent =
                'New profile photo selected. Save changes to apply it.';
        }
    });

    inspectorRemovePhotoBtn?.addEventListener('click', function () {
        if (inspectorPhotoInput) {
            inspectorPhotoInput.value = '';
        }

        if (inspectorRemovePhotoInput) {
            inspectorRemovePhotoInput.value = '1';
        }

        showInspectorInitials();

        if (inspectorPhotoStatus) {
            inspectorPhotoStatus.textContent =
                'Profile photo will be removed when you save changes.';
        }
    });


    const successToast = document.getElementById('inspectorSuccessToast');

    function hideSuccessToast() {
        if (!successToast || successToast.classList.contains('is-hiding')) {
            return;
        }

        successToast.classList.add('is-hiding');

        window.setTimeout(() => {
            successToast.remove();
        }, 170);
    }

    successToast?.querySelector('[data-toast-close]')?.addEventListener(
        'click',
        hideSuccessToast
    );

    if (successToast) {
        window.setTimeout(hideSuccessToast, 3800);
    }

    window.addEventListener('resize', () => {
        if (!isMobile()) {
            closeMobileSidebar();
        }

        applySavedSidebarState();
    });
})();