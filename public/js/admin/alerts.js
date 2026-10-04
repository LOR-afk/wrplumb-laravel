(() => {
    const searchInput = document.getElementById('notificationSearchInput');
    const sortSelect = document.getElementById('notificationSortSelect');
    const list = document.getElementById('notificationList');
    const selectAll = document.getElementById('notificationSelectAll');

    if (!list) return;

    const rows = Array.from(list.querySelectorAll('.notification-row'));

    function filterRows() {
        const query = (searchInput?.value || '').trim().toLowerCase();

        rows.forEach((row) => {
            const haystack = (
                row.dataset.notificationSearch ||
                row.textContent ||
                ''
            ).toLowerCase();

            row.classList.toggle(
                'is-search-hidden',
                query !== '' && !haystack.includes(query)
            );
        });
    }

    function sortRows(mode) {
        const sorted = [...rows].sort((a, b) => {
            if (mode === 'oldest') {
                return Number(a.dataset.created || 0) - Number(b.dataset.created || 0);
            }

            if (mode === 'unread') {
                const unreadDifference =
                    Number(b.dataset.unread || 0) - Number(a.dataset.unread || 0);

                if (unreadDifference !== 0) {
                    return unreadDifference;
                }
            }

            return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
        });

        sorted.forEach((row) => list.appendChild(row));
    }

    searchInput?.addEventListener('input', filterRows);

    sortSelect?.addEventListener('change', () => {
        sortRows(sortSelect.value);
    });

    selectAll?.addEventListener('change', () => {
        document
            .querySelectorAll('.notification-item-check')
            .forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
    });
})();