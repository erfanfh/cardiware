import Swiper from 'swiper';
import {Navigation} from 'swiper/modules';
import 'swiper/css';

Swiper.use([Navigation]);

export const initProductImageGallerySwiper = () => {
    const root = document.querySelector('#product-image-gallery');

    if (!root) return;

    new Swiper(root, {
        loop: false,
        spaceBetween: 14,
        slidesPerView: 1.5,
        breakpoints: {
            640: {slidesPerView: 3.5},
            1024: {slidesPerView: 4.5},
        },
        navigation: {
            nextEl: '#product-image-gallery-pagination-next',
            prevEl: '#product-image-gallery-pagination-prev',
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initProductImageGallerySwiper();
    });
} else {
    initProductImageGallerySwiper();
}
