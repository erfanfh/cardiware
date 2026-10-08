document.addEventListener('DOMContentLoaded', () => {
    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');

    document.querySelectorAll('[data-footer-news]').forEach((ticker) => {
        const slides = [...ticker.querySelectorAll('[data-footer-news-slide]')];
        const controls = [...ticker.querySelectorAll('[data-footer-news-control]')];

        if (slides.length < 2) return;

        let active = 0;
        let timer;

        const show = (index) => {
            active = index;
            slides.forEach((slide, slideIndex) => {
                slide.hidden = slideIndex !== active;
            });
            controls.forEach((control, controlIndex) => {
                control.setAttribute('aria-current', controlIndex === active ? 'true' : 'false');
            });
        };

        const pause = () => {
            window.clearInterval(timer);
            timer = undefined;
        };

        const play = () => {
            pause();
            if (motionPreference.matches || document.hidden || ticker.matches(':hover') || ticker.contains(document.activeElement)) return;
            timer = window.setInterval(() => show((active + 1) % slides.length), 4200);
        };

        controls.forEach((control, index) => {
            control.addEventListener('click', () => {
                show(index);
                play();
            });
        });

        ticker.addEventListener('mouseenter', pause);
        ticker.addEventListener('mouseleave', play);
        ticker.addEventListener('focusin', pause);
        ticker.addEventListener('focusout', () => window.setTimeout(play, 0));
        document.addEventListener('visibilitychange', play);
        motionPreference.addEventListener('change', play);

        play();
    });
});
