import Swiper from 'swiper';
import {Autoplay, Navigation} from 'swiper/modules';

import 'swiper/css';

function initHeroSwipers(scope = document) {
    const sliders = scope.querySelectorAll('.heroSwiper');

    sliders.forEach((sliderEl) => {
        // Prevent init again
        if (sliderEl.classList.contains('swiper-initialized')) {
            return;
        }

        const root = sliderEl.closest('.hero-slider') || sliderEl.parentElement;
        const nextButtons = root.querySelectorAll('.heroNext');
        const prevButtons = root.querySelectorAll('.heroPrev');
        const dotsWrappers = root.querySelectorAll('.heroDots');

        const swiper = new Swiper(sliderEl, {
            modules: [Navigation, Autoplay],
            loop: true,
            speed: 550,
            autoHeight: true,

            autoplay: {
                delay: 6500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },

            navigation: {
                nextEl: nextButtons[0] || null,
                prevEl: prevButtons[0] || null,
            },

            on: {
                init(sw) {
                    syncHeroDots(sw, dotsWrappers);
                    bindExtraNavButtons(sw, nextButtons, prevButtons);
                },
                slideChange(sw) {
                    syncHeroDots(sw, dotsWrappers);
                },
            },
        });

        sliderEl.querySelectorAll('img').forEach((image) => {
            if (!image.complete) {
                image.addEventListener('load', () => swiper.updateAutoHeight(), {once: true});
            }
        });
    });
}

function syncHeroDots(swiper, dotsWrappers) {
    const realSlidesCount = swiper.slides.filter(
        (slide) => !slide.classList.contains('swiper-slide-duplicate')
    ).length;

    const activeIndex = swiper.realIndex;

    dotsWrappers.forEach((wrapper) => {
        let dots = Array.from(wrapper.querySelectorAll('span'));

        if (dots.length !== realSlidesCount) {
            wrapper.innerHTML = '';

            for (let i = 0; i < realSlidesCount; i++) {
                const dot = document.createElement('span');
                dot.className = 'h-2 rounded-full transition-all duration-300 cursor-pointer shrink-0';
                wrapper.appendChild(dot);
            }

            dots = Array.from(wrapper.querySelectorAll('span'));
        }

        dots.forEach((dot, index) => {
            const isActive = index === activeIndex;

            dot.classList.remove('bg-brand-400', 'bg-white/40', 'w-8', 'w-2');

            if (isActive) {
                dot.classList.add('bg-brand-400', 'w-8');
            } else {
                dot.classList.add('bg-white/40', 'w-2');
            }

            dot.onclick = () => {
                swiper.slideToLoop(index);
            };
        });
    });
}

function bindExtraNavButtons(swiper, nextButtons, prevButtons) {
    // Since buttons repeat in each slide, only the first gets navigation official
    // the others manually bind are set
    nextButtons.forEach((btn, index) => {
        if (index === 0) return;

        btn.addEventListener('click', () => {
            swiper.slideNext();
        });
    });

    prevButtons.forEach((btn, index) => {
        if (index === 0) return;

        btn.addEventListener('click', () => {
            swiper.slidePrev();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initHeroSwipers(document);
});

// If you later Livewire / Ajax / Elementor have it, call again:
// initHeroSwipers(containerElement);

export {initHeroSwipers};
