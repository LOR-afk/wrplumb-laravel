(() => {
    const sortSelect = document.getElementById('supportSortSelect');
    const ticketList = document.getElementById('supportTicketList');
    const chatBody = document.getElementById('adminSupportChatBody');

    if (chatBody) {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    if (!sortSelect || !ticketList) {
        return;
    }

    const rows = Array.from(ticketList.querySelectorAll('.support-ticket-row'));

    function sortRows(mode) {
        const sorted = [...rows].sort((a, b) => {
            if (mode === 'oldest') {
                return Number(a.dataset.created || 0) - Number(b.dataset.created || 0);
            }

            if (mode === 'priority') {
                const byPriority =
                    Number(b.dataset.priority || 0) - Number(a.dataset.priority || 0);

                if (byPriority !== 0) {
                    return byPriority;
                }
            }

            return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
        });

        sorted.forEach((row) => ticketList.appendChild(row));
    }

    sortSelect.addEventListener('change', () => sortRows(sortSelect.value));
})();
