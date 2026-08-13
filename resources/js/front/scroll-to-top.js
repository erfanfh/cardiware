document.addEventListener('DOMContentLoaded', function () {
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');

    if (!scrollToTopBtn) {
        return;
    }

    let stickyBuyBarVisible = false;
    let stickyBuyBarHeight = 0;

    const getVisibleStickyBuyBar = () => {
        if (!stickyBuyBarVisible) {
            return null;
        }

        return document.querySelector('[data-product-sticky-buy-bar]:not([style*="display: none"])');
    };

    const rangesOverlap = (startA, endA, startB, endB) => {
        return startA < endB && endA > startB;
    };

    const syncBottomOffset = () => {
        const stickyBuyBar = getVisibleStickyBuyBar();

        if (!stickyBuyBar || stickyBuyBarHeight <= 0) {
            scrollToTopBtn.style.bottom = '';
            return;
        }

        const buttonRect = scrollToTopBtn.getBoundingClientRect();
        const stickyBarRect = stickyBuyBar.getBoundingClientRect();
        const horizontalSafetyGap = 8;

        const overlapsHorizontally = rangesOverlap(
            buttonRect.left - horizontalSafetyGap,
            buttonRect.right + horizontalSafetyGap,
            stickyBarRect.left,
            stickyBarRect.right,
        );

        // On wider screens the sticky buy bar is centered and usually does not
        // occupy the scroll-to-top button area. Keep the button in its normal
        // position unless the two elements can actually collide.
        if (!overlapsHorizontally) {
            scrollToTopBtn.style.bottom = '';
            return;
        }

        const stickyBarBottomOffset = Number.parseFloat(window.getComputedStyle(stickyBuyBar).bottom) || 0;
        const gap = window.matchMedia('(min-width: 768px)').matches ? 24 : 16;

        scrollToTopBtn.style.bottom = `${stickyBuyBarHeight + stickyBarBottomOffset + gap}px`;
    };

    const toggleScrollButton = () => {
        const shouldShow = window.scrollY > 420;

        scrollToTopBtn.classList.toggle('opacity-100', shouldShow);
        scrollToTopBtn.classList.toggle('visible', shouldShow);
        scrollToTopBtn.classList.toggle('pointer-events-auto', shouldShow);
        scrollToTopBtn.classList.toggle('translate-y-0', shouldShow);
        scrollToTopBtn.classList.toggle('scale-100', shouldShow);

        scrollToTopBtn.classList.toggle('opacity-0', !shouldShow);
        scrollToTopBtn.classList.toggle('invisible', !shouldShow);
        scrollToTopBtn.classList.toggle('pointer-events-none', !shouldShow);
        scrollToTopBtn.classList.toggle('translate-y-4', !shouldShow);
        scrollToTopBtn.classList.toggle('scale-95', !shouldShow);
    };

    window.addEventListener('scroll', toggleScrollButton, {passive: true});
    window.addEventListener('resize', syncBottomOffset);

    window.addEventListener('product-sticky-buy-bar-visibility', (event) => {
        const detail = event?.detail ?? {};

        stickyBuyBarVisible = Boolean(detail.visible);
        stickyBuyBarHeight = stickyBuyBarVisible
            ? Math.max(0, Number(detail.height ?? 0))
            : 0;

        requestAnimationFrame(syncBottomOffset);
    });

    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    });

    toggleScrollButton();
    syncBottomOffset();
});
