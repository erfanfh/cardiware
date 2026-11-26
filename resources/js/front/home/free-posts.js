import Swiper from 'swiper';
import {Autoplay, Pagination} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/pagination';

// Large card carousel: only when more than 3 cards (pinned + New)
// Pattern .mwFreeHeroCards in Blade Conditional render completed.
function initFreeHeroCardsSwiper() {
    document.querySelectorAll('.mwFreeHeroCards').forEach((element) => {
        if (element.swiper) {
            element.swiper.update();
            return;
        }

        new Swiper(element, {
            modules: [Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 16,
            loop: false,
            speed: 700,
            watchOverflow: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: element.querySelector('.mwFreeHeroPagination'),
                clickable: true,
                renderBullet(index, className) {
                    return `<span class="${className} transition-all duration-300"></span>`;
                },
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 14,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 16,
                },
            },
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFreeHeroCardsSwiper);
} else {
    initFreeHeroCardsSwiper();
}
document.addEventListener('livewire:navigated', initFreeHeroCardsSwiper);

document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morphed', initFreeHeroCardsSwiper);
    Livewire.hook('morph.removing', ({el}) => {
        if (el.matches('.mwFreeHeroCards')) {
            el.swiper?.destroy(true, true);
        }
        el.querySelectorAll('.mwFreeHeroCards').forEach(element => element.swiper?.destroy(true, true));
    });
});
