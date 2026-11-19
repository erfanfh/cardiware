import Swiper from 'swiper';
import {Autoplay, Navigation, Pagination} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/pagination';

// اسلایدر محصولات دانلودی صفحهٔ اصلی — فقط وقتی کلاس .mwHomeProductsSlider render شده
// (تنظیمات ادمین: download_products_slider = true) فعال می‌شود.
function initHomeProductsSlider() {
    document.querySelectorAll('.mwHomeProductsSlider').forEach((element) => {
        if (element.swiper) return;

        const section = element.closest('#digital-products');
        const prevBtn = section?.querySelector('[data-mw-products-prev]');
        const nextBtn = section?.querySelector('[data-mw-products-next]');
        const pagination = element.querySelector('.mwHomeProductsPagination');

        new Swiper(element, {
            modules: [Navigation, Pagination, Autoplay],
            direction: 'horizontal',
            grabCursor: true,
            watchOverflow: true,
            spaceBetween: 16,
            slidesPerView: 1.15,
            speed: 650,

            navigation: prevBtn && nextBtn ? {
                prevEl: prevBtn,
                nextEl: nextBtn,
            } : false,

            pagination: pagination ? {
                el: pagination,
                clickable: true,
                renderBullet(index, className) {
                    return `<span class="${className} transition-all duration-300"></span>`;
                },
            } : false,

            autoplay: !window.matchMedia('(prefers-reduced-motion: reduce)').matches && {
                delay: 4500,
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
                    slidesPerView: 3,
                },
                1280: {
                    slidesPerView: 4,
                },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', initHomeProductsSlider);
document.addEventListener('livewire:navigated', initHomeProductsSlider);
document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morph.updated', initHomeProductsSlider);
});
