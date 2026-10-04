(() => {
    const STORAGE_KEY = 'wrplumb.client.sidebarCollapsed';
    const body = document.body;
    const isSupportWidget = body.classList.contains('support-widget-mode');
    const mobileBreakpoint = 992;

    const sidebarToggle = document.getElementById('clientSidebarToggle');
    const sidebarOverlay = document.getElementById('clientSidebarOverlay');
    const profileToggle = document.getElementById('clientProfileToggle');
    const profileMenu = document.getElementById('clientProfileMenu');
    const alertToggle = document.getElementById('clientAlertToggle');
    const alertMenu = document.getElementById('clientAlertMenu');
    const searchInput = document.getElementById('clientGlobalSearch');

    const isMobile = () => window.innerWidth < mobileBreakpoint;

    function applySavedSidebarState() {
        if (isSupportWidget || isMobile()) {
            body.classList.remove('client-sidebar-collapsed');
            return;
        }

        const saved = localStorage.getItem(STORAGE_KEY);
        const collapsed = saved === null ? true : saved === '1';

        body.classList.toggle('client-sidebar-collapsed', collapsed);
        sidebarToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function closeClientSidebar() {
        body.classList.remove('client-sidebar-open');
        if (isMobile()) {
            sidebarToggle?.setAttribute('aria-expanded', 'false');
        }
    }

    function toggleSidebar() {
        if (isMobile()) {
            const open = body.classList.toggle('client-sidebar-open');
            sidebarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
            return;
        }

        body.classList.toggle('client-sidebar-collapsed');

        const collapsed = body.classList.contains('client-sidebar-collapsed');

        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        sidebarToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function closeProfileMenu() {
        if (!profileToggle || !profileMenu) return;

        profileMenu.classList.remove('open');
        profileMenu.setAttribute('aria-hidden', 'true');
        profileToggle.setAttribute('aria-expanded', 'false');
    }

    function closeAlertMenu() {
        if (!alertToggle || !alertMenu) return;

        alertMenu.classList.remove('open');
        alertMenu.setAttribute('aria-hidden', 'true');
        alertToggle.setAttribute('aria-expanded', 'false');
    }

    function filterCurrentPage(query) {
        const normalized = query.trim().toLowerCase();
        const searchable = document.querySelectorAll('[data-client-search]');

        searchable.forEach((element) => {
            const haystack = (
                element.dataset.clientSearch ||
                element.textContent ||
                ''
            ).toLowerCase();

            element.classList.toggle(
                'is-client-search-hidden',
                normalized !== '' && !haystack.includes(normalized)
            );
        });
    }

    if (!isSupportWidget) {
        applySavedSidebarState();

        sidebarToggle?.addEventListener('click', toggleSidebar);
        sidebarOverlay?.addEventListener('click', closeClientSidebar);

        document.querySelectorAll('#clientSidebar .sidebar-link').forEach((link) => {
            link.addEventListener('click', closeClientSidebar);
        });

        profileToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            closeAlertMenu();

            const isOpen = profileMenu.classList.toggle('open');

            profileMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            profileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.querySelectorAll(
            '[data-bs-target="#clientViewProfileModal"], [data-bs-target="#clientAccountSettingsModal"]'
        ).forEach((button) => {
            button.addEventListener('click', closeProfileMenu);
        });

        alertToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            closeProfileMenu();

            const isOpen = alertMenu.classList.toggle('open');

            alertMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            alertToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        alertMenu?.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.client-profile-dropdown')) {
                closeProfileMenu();
            }

            if (!event.target.closest('.client-alert-dropdown')) {
                closeAlertMenu();
            }
        });

        searchInput?.addEventListener('input', function () {
            filterCurrentPage(this.value);
        });
    }

    const supportToggle = document.getElementById('clientSupportToggle');
    const supportPanel = document.getElementById('clientSupportPanel');

    function closeSupportPanel() {
        if (!supportToggle || !supportPanel) return;

        supportPanel.classList.remove('open');
        supportToggle.classList.remove('open');
        supportPanel.setAttribute('aria-hidden', 'true');
        supportToggle.setAttribute('aria-label', 'Open customer support');
    }

    supportToggle?.addEventListener('click', () => {
        if (!supportPanel) return;

        const isOpen = supportPanel.classList.toggle('open');

        supportToggle.classList.toggle('open', isOpen);
        supportPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        supportToggle.setAttribute(
            'aria-label',
            isOpen ? 'Close customer support' : 'Open customer support'
        );
    });

    const photoInput = document.getElementById('clientProfilePhotoInput');
    const photoPreview = document.getElementById('clientProfilePhotoPreview');
    const removePhotoInput = document.getElementById('clientRemoveProfilePhoto');
    const removePhotoButton = document.getElementById('clientRemoveProfilePhotoButton');
    const photoStatus = document.getElementById('clientProfilePhotoStatus');

    if (photoPreview) {
        const initials = photoPreview.dataset.initials || 'C';

        const showInitials = (message) => {
            photoPreview.innerHTML = '<span>' + initials + '</span>';
            if (photoStatus && message) photoStatus.textContent = message;
        };

        const showImage = (src, message) => {
            photoPreview.innerHTML =
                '<img src="' + src + '" alt="Profile photo preview">';

            if (photoStatus && message) photoStatus.textContent = message;
        };

        photoInput?.addEventListener('change', function () {
            const file = this.files?.[0];

            if (!file) return;

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.value = '';
                if (photoStatus) {
                    photoStatus.textContent = 'Please choose a JPG, PNG, or WEBP image.';
                }
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                this.value = '';
                if (photoStatus) {
                    photoStatus.textContent = 'Profile photo must not exceed 2 MB.';
                }
                return;
            }

            const reader = new FileReader();

            reader.addEventListener('load', (event) => {
                showImage(
                    event.target.result,
                    'New photo selected. Click Save Changes to apply it.'
                );

                if (removePhotoInput) removePhotoInput.value = '0';
            });

            reader.readAsDataURL(file);
        });

        removePhotoButton?.addEventListener('click', () => {
            if (photoInput) photoInput.value = '';
            if (removePhotoInput) removePhotoInput.value = '1';

            showInitials(
                'Photo marked for removal. Click Save Changes to apply it.'
            );
        });
    }

    document.querySelectorAll('.client-toast-close').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('.client-toast')?.remove();
        });
    });

    if (document.querySelector('.client-toast')) {
        window.setTimeout(() => {
            document.querySelectorAll('.client-toast').forEach((toast) => {
                toast.classList.add('leaving');

                window.setTimeout(() => {
                    toast.remove();
                }, 220);
            });
        }, 3800);
    }

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            if (!searchInput) return;

            event.preventDefault();
            searchInput.focus();
            searchInput.select();
        }

        if (event.key === 'Escape') {
            closeClientSidebar();
            closeProfileMenu();
            closeAlertMenu();
            closeSupportPanel();

            if (document.activeElement === searchInput) {
                searchInput.value = '';
                filterCurrentPage('');
                searchInput.blur();
            }
        }
    });

    window.addEventListener('resize', () => {
        if (isSupportWidget) return;

        if (!isMobile()) {
            closeClientSidebar();
        }

        applySavedSidebarState();
    });
})();
