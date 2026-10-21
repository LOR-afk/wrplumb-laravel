/**
 * Homepage JavaScript - Pure JavaScript
 * Handles homepage data loading and dynamic content
 */
// Set current year
document.addEventListener('DOMContentLoaded', function() {
    const currentYearElement = document.getElementById('current-year');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }
    // Free quotation: category -> service type dropdown
    initFreeQuotationServicePicker();
    initFreeQuotationFormGuard();
    initPublicAvailabilityCalendar();
    initQuotationLegalModals();
    // Free quotation: keep address editable and provide "open in maps" helper
    initFreeQuotationAddressMapLink();
    // Free quotation: address suggestions (API-backed)
    initFreeQuotationAddressSuggest();
    // Load homepage data
    loadHomepageData();
    // Free quotation handled by Laravel form submit
    // initFreeQuotationSubmit();
    // Pre-login homepage scroll animations
    initHomeScrollReveal();
    // Trimmed video ad clips on homepage
    initVideoAdClips();
});
function initVideoAdClips() {
    const videos = document.querySelectorAll('video.video-ad-clip[data-clip-start][data-clip-end]');
    if (!videos.length) return;
    videos.forEach(function (video) {
        // Apply optional playback restrictions via JS (avoids HTML validator warnings).
        try { video.disablePictureInPicture = true; } catch (e) {}
        try { video.disableRemotePlayback = true; } catch (e) {}
        try { video.controlsList = 'nodownload noplaybackrate noremoteplayback nofullscreen'; } catch (e) {}
        const start = Number(video.getAttribute('data-clip-start') || 0);
        const end = Number(video.getAttribute('data-clip-end') || 0);
        if (!Number.isFinite(start) || !Number.isFinite(end) || end <= start) return;
        function jumpToStart() {
            try { video.currentTime = start; } catch (e) {}
        }
        video.addEventListener('loadedmetadata', function () {
            jumpToStart();
        });
        video.addEventListener('timeupdate', function () {
            if (video.currentTime >= end) {
                jumpToStart();
                const p = video.play();
                if (p && typeof p.catch === 'function') p.catch(function () {});
            }
        });
    });
}
let homeRevealObserver = null;
function initHomeScrollReveal() {
    const selectors = [
        '.stats-section .stat-box',
        '#process .process-item',
        '#about .row > div',
        '#services .card',
        '#services .services-pills .nav-link',
        '#free-quotation .card',
        '#projects .project-card',
        '#contact .card',
        '#contact .map-card'
    ];
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const targets = document.querySelectorAll(selectors.join(','));
    targets.forEach(function (el, idx) {
        if (!el.classList.contains('reveal')) {
            el.classList.add('reveal');
        }
        // Alternate direction for better rhythm in multi-column rows
        if (el.closest('.row') && idx % 3 === 1) {
            el.classList.add('reveal-left');
        } else if (el.closest('.row') && idx % 3 === 2) {
            el.classList.add('reveal-right');
        }
    });
    if (prefersReducedMotion) {
        targets.forEach(function (el) { el.classList.add('is-visible'); });
        return;
    }
    if (!homeRevealObserver) {
        homeRevealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                } else {
                    // Replay animation when user scrolls away and back again
                    entry.target.classList.remove('is-visible');
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    }
    targets.forEach(function (el) {
        homeRevealObserver.observe(el);
    });
}
function initFreeQuotationAddressSuggest() {
    const addressInput = document.getElementById('fq-address');
    const box = document.getElementById('fq-address-suggestions');
    const latEl = document.getElementById('fq-address-lat');
    const lonEl = document.getElementById('fq-address-lon');
    if (!addressInput || !box) return;
    let timer = null;
    let lastQuery = '';
    let activeController = null;
    function hideBox() {
        box.classList.add('d-none');
        box.innerHTML = '';
    }
    function showBox() {
        box.classList.remove('d-none');
    }
    function clearLatLon() {
        if (latEl) latEl.value = '';
        if (lonEl) lonEl.value = '';
    }
    function setLatLon(lat, lon) {
        if (latEl) latEl.value = lat || '';
        if (lonEl) lonEl.value = lon || '';
    }
    function render(results) {
        if (!results || !results.length) {
            hideBox();
            return;
        }
        box.innerHTML = results.map((r, idx) => {
            const safeLabel = String(r.label || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const lat = String(r.lat || '');
            const lon = String(r.lon || '');
            return `
                <button type="button" class="address-suggestion" data-idx="${idx}" data-lat="${lat}" data-lon="${lon}" data-label="${safeLabel}">
                    <i class="fas fa-map-marker-alt text-primary me-2"></i>${safeLabel}
                </button>
            `;
        }).join('');
        showBox();
    }
    async function fetchSuggest(q) {
        if (activeController) {
            try { activeController.abort(); } catch (e) {}
        }
        activeController = new AbortController();
        const endpoint = document.body?.dataset?.addressSuggestUrl || '';
        if (!endpoint) {
            return { success: false, results: [] };
        }
        const separator = endpoint.includes('?') ? '&' : '?';
        const res = await fetch(endpoint + separator + 'q=' + encodeURIComponent(q), {
            signal: activeController.signal,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) {
            return { success: false, results: [] };
        }
        return res.json();
    }
    function schedule() {
        const q = addressInput.value.trim();
        clearLatLon(); // only keep lat/lon when a suggestion is chosen
        if (q.length < 2) {
            hideBox();
            lastQuery = q;
            return;
        }
        if (q === lastQuery) return;
        lastQuery = q;
        if (timer) clearTimeout(timer);
        timer = setTimeout(async () => {
            try {
                const data = await fetchSuggest(q);
                if (data && data.success) {
                    render(data.results || []);
                } else {
                    hideBox();
                }
            } catch (e) {
                // aborted or network error: just hide suggestions
                hideBox();
            }
        }, 250);
    }
    addressInput.addEventListener('input', schedule);
    // Keep it clickable but don't "steal" typing focus
    box.addEventListener('mousedown', function (e) {
        e.preventDefault();
    });
    box.addEventListener('click', function (e) {
        const btn = e.target.closest('.address-suggestion');
        if (!btn) return;
        const labelText = btn.textContent.replace(/\s+/g, ' ').trim();
        addressInput.value = labelText;
        setLatLon(btn.getAttribute('data-lat'), btn.getAttribute('data-lon'));
        hideBox();
        addressInput.focus();
        // Trigger map link update
        addressInput.dispatchEvent(new Event('input', { bubbles: true }));
    });
    addressInput.addEventListener('blur', function () {
        // allow click selection before hiding
        setTimeout(hideBox, 150);
    });
    addressInput.addEventListener('focus', function () {
        // show again if user focuses and query is still valid
        if (addressInput.value.trim().length >= 2) schedule();
    });
}
function initFreeQuotationAddressMapLink() {
    const addressInput = document.getElementById('fq-address');
    const mapLink = document.getElementById('fq-address-map');
    if (!addressInput || !mapLink) return;
    function setLink(value) {
        const v = (value || '').trim();
        if (!v) {
            mapLink.classList.add('disabled');
            mapLink.setAttribute('aria-disabled', 'true');
            mapLink.setAttribute('tabindex', '-1');
            mapLink.href = '#';
            return;
        }
        mapLink.classList.remove('disabled');
        mapLink.setAttribute('aria-disabled', 'false');
        mapLink.removeAttribute('tabindex');
        mapLink.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(v);
    }
    // Initialize and update on typing
    setLink(addressInput.value);
    addressInput.addEventListener('input', function () {
        setLink(addressInput.value);
    });
}
function initFreeQuotationFormGuard() {
    const form = document.getElementById('free-quotation-form');
    const dateInput = document.getElementById('fq-preferred-date');
    const detailsInput = document.getElementById('fq-details');
    const detailsCount = document.getElementById('fq-details-count');
    const submitButton = document.getElementById('fq-submit-btn');
    if (!form) return;
    const minimumDetailsLength = 20;
    function localDateString() {
        const now = new Date();
        const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
        return local.toISOString().slice(0, 10);
    }
    function validateDate() {
        if (!dateInput || !dateInput.value) return true;
        const valid = dateInput.value >= localDateString();
        dateInput.setCustomValidity(valid ? '' : 'Preferred service date cannot be in the past.');
        return valid;
    }
    function validateDetails() {
        if (!detailsInput) return true;
        const trimmedLength = detailsInput.value.trim().length;
        if (detailsCount) detailsCount.textContent = String(detailsInput.value.length);
        const valid = trimmedLength >= minimumDetailsLength;
        detailsInput.setCustomValidity(
            valid ? '' : `Please provide at least ${minimumDetailsLength} characters describing the problem or work needed.`
        );
        return valid;
    }
    if (dateInput) {
        dateInput.min = localDateString();
        dateInput.addEventListener('change', validateDate);
        dateInput.addEventListener('input', validateDate);
    }
    if (detailsInput) {
        validateDetails();
        detailsInput.addEventListener('input', validateDetails);
    }
    form.addEventListener('submit', function (event) {
        if (event.defaultPrevented) return;
        const dateValid = validateDate();
        const detailsValid = validateDetails();
        if (!dateValid || !detailsValid || !form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }
        if (form.dataset.submitting === '1') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = '1';
        form.setAttribute('aria-busy', 'true');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-disabled', 'true');
            submitButton.classList.add('is-loading');
            submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending Request...';
        }
    });
}
function initPublicAvailabilityCalendar() {
    const picker = document.getElementById('fq-availability-picker');
    const panel = document.getElementById('fq-availability-calendar');
    const trigger = document.getElementById('fq-preferred-date-trigger');
    const hiddenInput = document.getElementById('fq-preferred-date');
    const display = document.getElementById('fq-preferred-date-display');
    const monthLabel = document.getElementById('fq-calendar-month-label');
    const daysContainer = document.getElementById('fq-calendar-days');
    const status = document.getElementById('fq-calendar-status');
    const prevButton = document.getElementById('fq-calendar-prev');
    const nextButton = document.getElementById('fq-calendar-next');
    const clearButton = document.getElementById('fq-calendar-clear');
    const legendItems = picker.querySelectorAll('.fq-calendar-legend span');
    if (legendItems[0]) legendItems[0].innerHTML = '<i class="fq-legend-dot available"></i>Slots available';
    if (legendItems[1]) legendItems[1].innerHTML = '<i class="fq-legend-dot unavailable"></i>Full / unavailable';
    if (!picker || !panel || !trigger || !hiddenInput || !daysContainer) return;
    const endpoint = picker.dataset.availabilityUrl || '';
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const selectedValue = picker.dataset.selectedDate || hiddenInput.value || '';
    let selectedDate = selectedValue ? parseDateValue(selectedValue) : null;
    let visibleMonth = selectedDate
        ? new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1)
        : new Date(today.getFullYear(), today.getMonth(), 1);
    let availability = {};
    let requestController = null;
    function parseDateValue(value) {
        const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) return null;
        return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
    }
    function formatDateValue(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    function formatDisplayDate(date) {
        return new Intl.DateTimeFormat('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }).format(date);
    }
    function formatMonthParam(date) {
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    }
    function updateSelectedDisplay() {
        if (selectedDate) {
            hiddenInput.value = formatDateValue(selectedDate);
            display.textContent = formatDisplayDate(selectedDate);
            trigger.classList.add('has-value');
        } else {
            hiddenInput.value = '';
            display.textContent = 'Choose an available date';
            trigger.classList.remove('has-value');
        }
    }
    function setStatus(message, type = '') {
        if (!status) return;
        status.textContent = message;
        status.className = `fq-calendar-status ${type}`.trim();
    }
    function renderCalendar() {
        const year = visibleMonth.getFullYear();
        const month = visibleMonth.getMonth();
        const firstDay = new Date(year, month, 1);
        const totalDays = new Date(year, month + 1, 0).getDate();
        const currentMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        monthLabel.textContent = new Intl.DateTimeFormat('en-PH', {
            month: 'long',
            year: 'numeric'
        }).format(visibleMonth);
        prevButton.disabled = visibleMonth <= currentMonth;
        daysContainer.innerHTML = '';
        for (let i = 0; i < firstDay.getDay(); i += 1) {
            const spacer = document.createElement('span');
            spacer.className = 'fq-calendar-spacer';
            daysContainer.appendChild(spacer);
        }
        for (let day = 1; day <= totalDays; day += 1) {
            const date = new Date(year, month, day);
            const dateKey = formatDateValue(date);
            const dayAvailability = availability[dateKey] || {};
            const remainingSlots = Number(dayAvailability.remaining_slots || 0);
            const isFull = Boolean(dayAvailability.full) || remainingSlots < 1;
            const isPast = date < today;
            const isAvailable = !isPast && !isFull;
            const isSelected = selectedDate && formatDateValue(selectedDate) === dateKey;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'fq-calendar-day';
            button.dataset.date = dateKey;
            if (isPast) button.classList.add('is-past');
            if (!isPast && isFull) button.classList.add('is-unavailable');
            if (isAvailable) button.classList.add('is-available');
            if (isSelected) button.classList.add('is-selected');
            button.disabled = !isAvailable;
            button.innerHTML = `
                <span class="fq-calendar-day-number">${day}</span>
                <span class="fq-calendar-day-capacity">
                    ${isPast
                        ? 'Past'
                        : isAvailable
                            ? `<i class="fq-capacity-dot"></i>${remainingSlots} ${remainingSlots === 1 ? 'slot' : 'slots'} left`
                            : 'FULL'}
                </span>
            `;
            if (isAvailable) {
                button.addEventListener('click', function () {
                    selectedDate = date;
                    updateSelectedDisplay();
                    renderCalendar();
                    panel.classList.add('d-none');
                    trigger.setAttribute('aria-expanded', 'false');
                });
            }
            daysContainer.appendChild(button);
        }
    }
    async function loadAvailability() {
        if (!endpoint) {
            availability = {};
            setStatus('Availability data is currently unavailable.', 'is-error');
            renderCalendar();
            return;
        }
        if (requestController) requestController.abort();
        requestController = new AbortController();
        setStatus('Checking inspector availability...', 'is-loading');
        try {
            const separator = endpoint.includes('?') ? '&' : '?';
            const response = await fetch(
                `${endpoint}${separator}month=${encodeURIComponent(formatMonthParam(visibleMonth))}`,
                {
                    signal: requestController.signal,
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' }
                }
            );
            if (!response.ok) throw new Error(`Availability request failed with ${response.status}`);
            const data = await response.json();
            availability = data && data.availability ? data.availability : {};
            setStatus('Select a date with an available service slot.', 'is-success');
            renderCalendar();
        } catch (error) {
            if (error.name === 'AbortError') return;
            availability = {};
            setStatus('Could not load availability. You may submit without a preferred date.', 'is-error');
            renderCalendar();
        }
    }
    trigger.addEventListener('click', function () {
        const opening = panel.classList.contains('d-none');
        panel.classList.toggle('d-none', !opening);
        trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
        if (opening) loadAvailability();
    });
    prevButton.addEventListener('click', function () {
        visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
        loadAvailability();
    });
    nextButton.addEventListener('click', function () {
        visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
        loadAvailability();
    });
    clearButton?.addEventListener('click', function () {
        selectedDate = null;
        updateSelectedDisplay();
        renderCalendar();
    });
    document.addEventListener('click', function (event) {
        if (!picker.contains(event.target)) {
            panel.classList.add('d-none');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });
    updateSelectedDisplay();
}
function initQuotationLegalModals() {
    const quoteModalElement = document.getElementById('quoteModal');
    const legalModalElement = document.getElementById('fqLegalModal');
    const legalTitle = document.getElementById('fqLegalModalLabel');
    const legalBody = document.getElementById('fqLegalModalBody');
    const fullPageLink = document.getElementById('fqLegalFullPageLink');
    if (!quoteModalElement || !legalModalElement || !legalBody) return;
    let reopenQuoteModal = false;
    let pendingUrl = '';
    let pendingTitle = '';
    async function loadLegalContent(url, title) {
        legalTitle.textContent = title || 'Legal Information';
        fullPageLink.href = url;
        fullPageLink.classList.remove('d-none');
        legalBody.innerHTML = `
            <div class="fq-legal-loading">
                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                Loading...
            </div>
        `;
        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: 'text/html' }
            });
            if (!response.ok) throw new Error(`Legal page returned ${response.status}`);
            const html = await response.text();
            const documentFragment = new DOMParser().parseFromString(html, 'text/html');
            const legalContent = documentFragment.querySelector(
                '[data-legal-content], .legal-card, .legal-content-card, .legal-main-card, main article'
            );
            if (legalContent) {
                legalBody.innerHTML = legalContent.innerHTML;
                return;
            }
            const main = documentFragment.querySelector('main');
            if (!main) throw new Error('Legal content not found');
            const fallback = main.cloneNode(true);
            fallback.querySelectorAll(
                'nav, header, footer, .legal-back-btn, .legal-page-sidebar, .legal-sidebar, .legal-hero, .legal-nav, .on-this-page'
            ).forEach(function (element) {
                element.remove();
            });
            legalBody.innerHTML = fallback.innerHTML;
        } catch (error) {
            legalBody.innerHTML = `
                <div class="alert alert-warning mb-0">
                    The legal information could not be loaded here.
                    Use the "Open full page" button to view it.
                </div>
            `;
        }
    }
    document.querySelectorAll('.fq-legal-link[data-legal-modal-url]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (typeof bootstrap === 'undefined') return;
            event.preventDefault();
            pendingUrl = link.dataset.legalModalUrl || link.href;
            pendingTitle = link.dataset.legalModalTitle || link.textContent.trim();
            reopenQuoteModal = quoteModalElement.classList.contains('show');
            const quoteInstance = bootstrap.Modal.getOrCreateInstance(quoteModalElement);
            if (reopenQuoteModal) {
                const onHidden = function () {
                    quoteModalElement.removeEventListener('hidden.bs.modal', onHidden);
                    loadLegalContent(pendingUrl, pendingTitle);
                    bootstrap.Modal.getOrCreateInstance(legalModalElement).show();
                };
                quoteModalElement.addEventListener('hidden.bs.modal', onHidden);
                quoteInstance.hide();
                return;
            }
            loadLegalContent(pendingUrl, pendingTitle);
            bootstrap.Modal.getOrCreateInstance(legalModalElement).show();
        });
    });
    legalModalElement.addEventListener('hidden.bs.modal', function () {
        if (!reopenQuoteModal || typeof bootstrap === 'undefined') return;
        reopenQuoteModal = false;
        bootstrap.Modal.getOrCreateInstance(quoteModalElement).show();
    });
}
function initFreeQuotationServicePicker() {
    const categorySelect = document.getElementById('fq-service-category');
    const serviceTypeSelect = document.getElementById('fq-service-type');
    if (!categorySelect || !serviceTypeSelect) return;
    const FALLBACK_SERVICES = {
        plumbing: [
            'Plumbing & Repair',
            'Waste Line Installation',
            'Water Line Installation',
            'Downspout & Sewer Line Installation',
            'Transfer & Jockey Pump Installation',
            'Plumbing Fixtures & Accessories Installation',
            'Fire Sprinkler System Installation'
        ],
        construction: [
            'New Home & Commercial Building & Renovation',
            'Masonry Works',
            'Carpentry',
            'Finishing Works',
            'Tile Installation',
            'Steel Works',
            'New & Renovation Paint Works'
        ]
    };
    const CATEGORY_KEYS = ['plumbing', 'construction'];
    let servicesCatalog = normalizeCatalog(FALLBACK_SERVICES);
    function formatServiceType(value) {
        const raw = String(value || '').trim();
        if (!raw) return '';
        return raw
            .replace(/[_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .toLowerCase()
            .replace(/\b[a-z]/g, function (ch) { return ch.toUpperCase(); });
    }
    function resetServiceTypeSelect(placeholderText) {
        serviceTypeSelect.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholderText || 'Select a service';
        opt.disabled = true;
        opt.selected = true;
        serviceTypeSelect.appendChild(opt);
        serviceTypeSelect.disabled = true;
    }
    function normalizeCatalog(source) {
        const normalized = {
            plumbing: [],
            construction: []
        };
        if (!source || typeof source !== 'object') {
            return normalized;
        }
        CATEGORY_KEYS.forEach(category => {
            const list = Array.isArray(source[category]) ? source[category] : [];
            list.forEach(item => {
                const name = String(item || '').trim();
                if (name !== '' && !normalized[category].includes(name)) {
                    normalized[category].push(name);
                }
            });
        });
        return normalized;
    }
    async function loadServicesCatalog() {
        const endpoint = document.body?.dataset?.servicesCatalogUrl || '';
        if (!endpoint) {
            return;
        }
        try {
            const response = await fetch(endpoint, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            if (data && data.success && data.services) {
                servicesCatalog = normalizeCatalog(data.services);
            }
        } catch (e) {
            // Keep fallback values when the optional API is unavailable.
        }
    }
    function populateServiceTypes(category) {
        const list = servicesCatalog[category] || [];
        serviceTypeSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select a service';
        placeholder.disabled = true;
        placeholder.selected = true;
        serviceTypeSelect.appendChild(placeholder);
        list.forEach(name => {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = formatServiceType(name) || name;
            serviceTypeSelect.appendChild(opt);
        });
        serviceTypeSelect.disabled = list.length === 0;
    }
    // Init state
    resetServiceTypeSelect('Select a category first');
    categorySelect.addEventListener('change', function () {
        populateServiceTypes(categorySelect.value);
    });
    loadServicesCatalog().then(function () {
        if (categorySelect.value) {
            populateServiceTypes(categorySelect.value);
        }
    });
}
/**
 * Load homepage data from API
 */
function loadHomepageData() {
    const endpoint = document.body?.dataset?.homeApiUrl || '';
    if (!endpoint) {
        return;
    }
    fetch(endpoint, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Homepage API returned ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                updateStatistics(data.stats || {});
                displayFeaturedProjects(data.featured_projects || []);
            }
        })
        .catch(error => {
            console.warn('Homepage data API unavailable:', error);
        });
}
/**
 * Update statistics on the page
 */
function updateStatistics(stats) {
    const completedProjects = document.getElementById('stat-completed-projects');
    const happyClients = document.getElementById('stat-happy-clients');
    const servicesCompleted = document.getElementById('stat-services-completed');
    const activeProjects = document.getElementById('stat-active-projects');
    if (completedProjects) completedProjects.textContent = stats.completed_projects || 0;
    if (happyClients) happyClients.textContent = (stats.happy_clients || 0) + '+';
    if (servicesCompleted) servicesCompleted.textContent = (stats.services_completed || 0) + '+';
    if (activeProjects) activeProjects.textContent = stats.active_projects || 0;
}
/**
 * Display featured projects
 */
function displayFeaturedProjects(projects) {
    const projectsContainer = document.getElementById('projects-container');
    if (!projectsContainer) return;
    if (projects && projects.length > 0) {
        let projectsHtml = '<div class="row">';
        projects.forEach(project => {
            projectsHtml += `
                <div class="col-md-4 mb-4">
                    <div class="card project-card reveal">
                        <div class="card-body">
                            <h5 class="card-title">${escapeHtml(project.name)}</h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-user"></i> ${escapeHtml(project.client_name || 'Client')}
                            </p>
                            ${project.description ? `<p class="card-text">${escapeHtml(project.description_short)}</p>` : ''}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="badge bg-success">Completed</span>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> ${project.created_date}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        projectsHtml += '</div>';
        projectsContainer.innerHTML = projectsHtml;
        // Newly inserted cards need observer hookup.
        initHomeScrollReveal();
    }
}
/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
