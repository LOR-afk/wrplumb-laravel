(() => {
    function initJobOrders() {
        const form = document.getElementById('jobFilterForm');
        const list = document.getElementById('jobListContainer');
        const stats = document.getElementById('jobStatsContainer');
        const reset = document.getElementById('jobResetFilters');
        const search = form?.querySelector('input[name="search"]');
        const sortField = document.getElementById('jobSortField');
        const directionField = document.getElementById('jobDirectionField');

        if (!form || !list || !stats || !sortField || !directionField) {
            return;
        }

        let timer = null;
        let controller = null;

        function urlFromForm() {
            const url = new URL(form.action, window.location.origin);
            const data = new FormData(form);

            for (const [key, rawValue] of data.entries()) {
                const value = String(rawValue).trim();

                if (value !== '') {
                    url.searchParams.set(key, value);
                }
            }

            return url;
        }

        async function refresh(url = urlFromForm()) {
            if (controller) {
                controller.abort();
            }

            controller = new AbortController();

            list.classList.add('is-loading');
            stats.classList.add('is-loading');

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: controller.signal
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                list.innerHTML = data.list_html;
                stats.innerHTML = data.stats_html;

                sortField.value =
                    url.searchParams.get('sort') ||
                    sortField.value ||
                    'created_at';

                directionField.value =
                    url.searchParams.get('direction') ||
                    directionField.value ||
                    'desc';

                window.history.replaceState(
                    {},
                    '',
                    url.toString()
                );
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Job Orders AJAX:', error);
                }
            } finally {
                list.classList.remove('is-loading');
                stats.classList.remove('is-loading');
            }
        }

        if (search) {
            search.addEventListener('input', () => {
                clearTimeout(timer);

                timer = setTimeout(() => {
                    refresh();
                }, 80);
            });

            search.addEventListener('search', () => {
                refresh();
            });
        }

        form.querySelectorAll(
            'select[name="status"], ' +
            'select[name="service_type"], ' +
            'select[name="worker_id"]'
        ).forEach((select) => {
            select.addEventListener('change', () => {
                refresh();
            });
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            refresh();
        });

        reset?.addEventListener('click', () => {
            form.reset();

            sortField.value = 'created_at';
            directionField.value = 'desc';

            refresh();
        });

        list.addEventListener('click', (event) => {
            const sortButton = event.target.closest(
                '[data-sort-column]'
            );

            if (sortButton) {
                event.preventDefault();

                const column = sortButton.dataset.sortColumn;

                const currentColumn =
                    sortField.value || 'created_at';

                const currentDirection =
                    directionField.value || 'desc';

                sortField.value = column;

                directionField.value =
                    currentColumn === column &&
                    currentDirection === 'asc'
                        ? 'desc'
                        : 'asc';

                refresh();

                return;
            }

            const paginationLink = event.target.closest(
                '.pagination a'
            );

            if (paginationLink) {
                event.preventDefault();

                refresh(
                    new URL(
                        paginationLink.href,
                        window.location.origin
                    )
                );
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initJobOrders,
            { once: true }
        );
    } else {
        initJobOrders();
    }
})();