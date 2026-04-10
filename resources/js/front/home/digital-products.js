import Swiper from 'swiper';
import {Autoplay, Pagination} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/pagination';

export function initDownloadProductsSwiper() {
    const elements = document.querySelectorAll('.js-download-products-swiper');

    elements.forEach((element) => {
        if (element.swiper) {
            return;
        }

        new Swiper(element, {
            modules: [Pagination, Autoplay],
            direction: 'horizontal',
            rtl: true,
            grabCursor: true,
            watchOverflow: true,
            spaceBetween: 16,
            slidesPerView: 1.15,
            speed: 600,

            pagination: {
                el: element.querySelector('.swiper-pagination'),
                clickable: true,
                dynamicBullets: true,
            },

            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },

            breakpoints: {
                640: {
                    slidesPerView: 1.4,
                },
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 2.4,
                },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDownloadProductsSwiper();
});
