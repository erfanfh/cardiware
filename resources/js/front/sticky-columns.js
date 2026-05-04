function initSmartSidebar() {
    const layout = document.querySelector('[data-smart-sidebar-layout]');

    if (!layout) {
        return;
    }

    const contentColumn = layout.querySelector('[data-smart-sidebar-content]');
    const sidebarColumn = layout.querySelector('[data-smart-sidebar-column]');
    const sidebarBox = layout.querySelector('[data-smart-sidebar-box]');

    if (!contentColumn || !sidebarColumn || !sidebarBox) {
        return;
    }

    const desktopQuery = window.matchMedia('(min-width: 1024px)');

    const topOffset = 154;
    const bottomOffset = 24;

    let lastScrollY = window.scrollY;
    let currentMode = 'normal';
    let ticking = false;

    const getDocumentTop = (element) => {
        return element.getBoundingClientRect().top + window.scrollY;
    };

    const getSidebarAbsoluteTop = () => {
        const sidebarRect = sidebarBox.getBoundingClientRect();
        const columnTop = getDocumentTop(sidebarColumn);

        return window.scrollY + sidebarRect.top - columnTop;
    };

    const reset = () => {
        currentMode = 'normal';

        sidebarColumn.style.position = '';
        sidebarColumn.style.minHeight = '';

        sidebarBox.style.position = '';
        sidebarBox.style.top = '';
        sidebarBox.style.bottom = '';
        sidebarBox.style.width = '';
    };

    const setNormal = () => {
        currentMode = 'normal';

        sidebarBox.style.position = 'relative';
        sidebarBox.style.top = '0px';
        sidebarBox.style.bottom = '';
        sidebarBox.style.width = '';
    };

    const setFixedTop = () => {
        currentMode = 'fixed-top';

        sidebarBox.style.position = 'fixed';
        sidebarBox.style.top = `${topOffset}px`;
        sidebarBox.style.bottom = '';
        sidebarBox.style.width = `${sidebarColumn.offsetWidth}px`;
    };

    const setFixedBottom = () => {
        currentMode = 'fixed-bottom';

        sidebarBox.style.position = 'fixed';
        sidebarBox.style.top = '';
        sidebarBox.style.bottom = `${bottomOffset}px`;
        sidebarBox.style.width = `${sidebarColumn.offsetWidth}px`;
    };

    const setAbsolute = (top) => {
        currentMode = 'absolute';

        sidebarBox.style.position = 'absolute';
        sidebarBox.style.top = `${top}px`;
        sidebarBox.style.bottom = '';
        sidebarBox.style.width = `${sidebarColumn.offsetWidth}px`;
    };

    const update = () => {
        ticking = false;

        if (!desktopQuery.matches) {
            reset();
            return;
        }

        const contentHeight = contentColumn.offsetHeight;
        const sidebarHeight = sidebarBox.offsetHeight;
        const viewportHeight = window.innerHeight;

        if (sidebarHeight >= contentHeight) {
            reset();
            return;
        }

        sidebarColumn.style.position = 'relative';
        sidebarColumn.style.minHeight = `${contentHeight}px`;

        const scrollY = window.scrollY;
        const direction = scrollY > lastScrollY ? 'down' : 'up';

        const columnTop = getDocumentTop(sidebarColumn);
        const columnBottom = columnTop + contentHeight;

        const maxAbsoluteTop = Math.max(contentHeight - sidebarHeight, 0);

        const sidebarRect = sidebarBox.getBoundingClientRect();

        const sidebarIsShorterThanViewport =
            sidebarHeight + topOffset + bottomOffset <= viewportHeight;

        if (sidebarIsShorterThanViewport) {
            if (scrollY + topOffset <= columnTop) {
                setNormal();
            } else if (scrollY + topOffset + sidebarHeight >= columnBottom) {
                setAbsolute(maxAbsoluteTop);
            } else {
                setFixedTop();
            }

            lastScrollY = scrollY;
            return;
        }

        if (scrollY <= columnTop - topOffset) {
            setNormal();
            lastScrollY = scrollY;
            return;
        }

        if (scrollY + viewportHeight - bottomOffset >= columnBottom) {
            setAbsolute(maxAbsoluteTop);
            lastScrollY = scrollY;
            return;
        }

        if (direction === 'down') {
            if (currentMode === 'fixed-top') {
                setAbsolute(getSidebarAbsoluteTop());
            }

            const currentRect = sidebarBox.getBoundingClientRect();

            if (currentRect.bottom <= viewportHeight - bottomOffset) {
                setFixedBottom();
            }
        }

        if (direction === 'up') {
            if (currentMode === 'fixed-bottom') {
                setAbsolute(getSidebarAbsoluteTop());
            }

            const currentRect = sidebarBox.getBoundingClientRect();

            if (currentRect.top >= topOffset) {
                setFixedTop();
            }
        }

        lastScrollY = scrollY;
    };

    const requestUpdate = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(update);
    };

    update();

    window.addEventListener('scroll', requestUpdate, {passive: true});
    window.addEventListener('resize', requestUpdate);

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(requestUpdate);

        observer.observe(contentColumn);
        observer.observe(sidebarBox);
    }
}

document.addEventListener('DOMContentLoaded', initSmartSidebar);
