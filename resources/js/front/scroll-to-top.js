document.addEventListener('DOMContentLoaded', function () {
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');

    if (!scrollToTopBtn) {
        return;
    }

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

    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    });

    toggleScrollButton();
});
