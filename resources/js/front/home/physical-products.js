import Swiper from 'swiper';
import {EffectCoverflow, Pagination} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/pagination';

document.addEventListener('DOMContentLoaded', () => {
    const el = document.querySelector('.physicalSwiper');

    if (!el) {
        return;
    }

    const total = el.querySelectorAll('.swiper-slide').length;
    const mid = Math.floor(total / 2);

    new Swiper(el, {
        modules: [EffectCoverflow, Pagination],

        effect: 'coverflow',
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        loop: true,
        spaceBetween: 30,

        pagination: {
            el: el.querySelector('.swiper-pagination'),
            clickable: true,
        },

        coverflowEffect: {
            rotate: 10,
            stretch: 120,
            depth: 200,
            modifier: 1,
            slideShadows: false,
        },

        on: {
            init(swiper) {
                swiper.slideToLoop(mid, 0, false);
            },
        },
    });
});
