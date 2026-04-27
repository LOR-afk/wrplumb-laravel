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

function initFreeQuotationServicePicker() {
    const categorySelect = document.getElementById('fq-service-category');
    const serviceTypeSelect = document.getElementById('fq-service-type');
    if (!categorySelect || !serviceTypeSelect) return;

    const SERVICES = {
        plumbing: [
            'Residential Plumbing & Repair',
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

    function populateServiceTypes(category) {
        const list = SERVICES[category] || [];

        serviceTypeSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select a service';
        placeholder.disabled = true;
        placeholder.selected = true;
        serviceTypeSelect.appendChild(placeholder);

        list.forEach(function (name) {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            serviceTypeSelect.appendChild(opt);
        });

        serviceTypeSelect.disabled = list.length === 0;
    }

    resetServiceTypeSelect('Select a category first');

    categorySelect.addEventListener('change', function () {
        populateServiceTypes(categorySelect.value);
    });

    if (categorySelect.value) {
        populateServiceTypes(categorySelect.value);
    }
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

        const res = await fetch('api/public/address_suggest.php?q=' + encodeURIComponent(q), {
            signal: activeController.signal,
            headers: { 'Accept': 'application/json' }
        });
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

function initFreeQuotationServicePicker() {
    const categorySelect = document.getElementById('fq-service-category');
    const serviceTypeSelect = document.getElementById('fq-service-type');
    if (!categorySelect || !serviceTypeSelect) return;

    const FALLBACK_SERVICES = {
        plumbing: [
            'Residential Plumbing & Repair',
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
        try {
            const response = await fetch('api/public/services_catalog.php', {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            });
            const data = await response.json();
            if (data && data.success && data.services) {
                const loaded = normalizeCatalog(data.services);
                servicesCatalog = loaded;
            }
        } catch (e) {
            // Keep fallback values when API is unavailable.
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
    fetch('api/public/home.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update statistics
                updateStatistics(data.stats);
                
                // Display featured projects
                displayFeaturedProjects(data.featured_projects);
            }
        })
        .catch(error => {
            console.error('Error loading homepage data:', error);
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





