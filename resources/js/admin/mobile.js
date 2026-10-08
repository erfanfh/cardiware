// Keep the compact list view in sync with Livewire's replaced rows.
function prepareAdminTables(root = document) {
    const tables = root.querySelectorAll?.('table') || [];

    tables.forEach((table) => {
        // A few older admin pages use plain tables instead of the shared list component.
        // Convert only simple tables with a header; complex spanning grids keep their scroll view.
        if (!table.classList.contains('admin-data-table')) {
            if (!table.querySelector('thead th') || table.querySelector('[rowspan]')) return;
            table.classList.add('admin-data-table');
            table.parentElement?.classList.add('admin-list-scroll');
        }

        const headings = [...table.querySelectorAll('thead tr:first-child > th')];
        const labels = headings.map((heading, index) => {
            const label = heading.querySelector(':scope > div > span')?.textContent
                || heading.textContent;
            return label.trim().replace(/\s+/g, ' ') || (index === 0 ? 'انتخاب' : 'جزئیات');
        });

        let hasSorting = false;
        headings.forEach((heading) => {
            const sortable = Boolean(heading.querySelector('[title*="مرتب‌سازی"]'));
            heading.classList.toggle('admin-mobile-sort', sortable);
            hasSorting ||= sortable;
        });

        table.classList.toggle('admin-has-sorting', hasSorting);

        table.querySelectorAll('tbody > tr').forEach((row) => {
            if (row.querySelector(':scope > td[colspan]')) {
                row.classList.add('admin-list-spanning-row');
                return;
            }

            [...row.children].forEach((cell, index) => {
                if (cell.tagName !== 'TD') return;
                const label = labels[index] || 'جزئیات';
                if (cell.dataset.mobileLabel !== label) cell.dataset.mobileLabel = label;
            });
        });

        table.classList.add('admin-mobile-ready');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const main = document.querySelector('.admin-main');
    if (!main) return;

    prepareAdminTables(main);

    let pending = false;
    const observer = new MutationObserver(() => {
        if (pending) return;
        pending = true;
        requestAnimationFrame(() => {
            pending = false;
            prepareAdminTables(main);
        });
    });

    observer.observe(main, { childList: true, subtree: true });
});
