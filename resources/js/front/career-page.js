const careerPage = document.querySelector('[data-career-page]');

if (careerPage) {
    const header = document.getElementById('header');
    const positionSelect = document.getElementById('career-position');
    const successModal = careerPage.querySelector('[data-career-success-modal]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    if (successModal) {
        successModal.showModal();
        successModal.querySelector('[data-career-modal-close]')?.addEventListener('click', () => successModal.close());
        successModal.addEventListener('click', event => {
            if (event.target !== successModal) return;

            const bounds = successModal.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right ||
                event.clientY < bounds.top || event.clientY > bounds.bottom) {
                successModal.close();
            }
        });
    }

    const updateScrollOffset = () => {
        const headerHeight = header?.getBoundingClientRect().height || 0;
        const headerTop = header ? parseFloat(window.getComputedStyle(header).top) || 0 : 0;
        careerPage.style.setProperty('--career-scroll-offset', `${Math.ceil(headerHeight + Math.max(headerTop, 0) + 16)}px`);
    };

    const selectPositionFromUrl = () => {
        if (!positionSelect) return;

        const requestedId = new URLSearchParams(window.location.search).get('position');
        positionSelect.value = requestedId && [...positionSelect.options].some(option => option.value === requestedId)
            ? requestedId
            : 'general';
    };

    const scrollToSection = (section, smooth = true) => {
        updateScrollOffset();
        const offset = parseFloat(careerPage.style.getPropertyValue('--career-scroll-offset')) || 0;
        const top = window.scrollY + section.getBoundingClientRect().top - offset;
        window.scrollTo({top, behavior: smooth && !reducedMotion.matches ? 'smooth' : 'instant'});
    };

    careerPage.addEventListener('click', event => {
        const link = event.target.closest('a[data-career-scroll]');
        if (!link || event.defaultPrevented || event.button !== 0 ||
            event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const url = new URL(link.href);
        const section = document.getElementById(url.hash.slice(1));
        if (url.origin !== window.location.origin ||
            url.pathname.replace(/\/$/, '') !== window.location.pathname.replace(/\/$/, '') || !section) return;

        event.preventDefault();
        if (link.dataset.careerPosition && positionSelect) {
            positionSelect.value = link.dataset.careerPosition;
        }
        window.history.pushState(null, '', url.pathname + url.search + url.hash);
        scrollToSection(section);
    });

    window.addEventListener('popstate', () => {
        selectPositionFromUrl();
        const section = document.getElementById(window.location.hash.slice(1));
        if (section) scrollToSection(section, false);
    });

    updateScrollOffset();
    if (header && 'ResizeObserver' in window) {
        new ResizeObserver(updateScrollOffset).observe(header);
    }

    if (window.location.hash) {
        const alignInitialSection = () => {
            const section = document.getElementById(window.location.hash.slice(1));
            if (section) scrollToSection(section, false);
        };

        if (document.readyState === 'complete') {
            requestAnimationFrame(alignInitialSection);
        } else {
            window.addEventListener('load', alignInitialSection, {once: true});
        }
    }
}
