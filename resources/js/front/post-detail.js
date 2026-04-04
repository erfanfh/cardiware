import Swiper from 'swiper';
import {Navigation} from 'swiper/modules';
import 'swiper/css';

Swiper.use([Navigation]);

const setActiveBullet = (paginationEl, activeIndex) => {
    const bullets = paginationEl.querySelectorAll('.swiper-pagination-bullet');

    bullets.forEach((bullet, index) => {
        bullet.classList.remove('w-8', 'swiper-pagination-bullet-active');
        bullet.classList.add('w-2');

        if (index === activeIndex) {
            bullet.classList.add('w-8', 'swiper-pagination-bullet-active');
            bullet.classList.remove('w-2');
        }
    });
};

const getActiveBulletIndex = (swiper) => {
    const lastIndex = swiper.slides.length - 1;

    if (swiper.isEnd) {
        return lastIndex;
    }

    return Math.min(swiper.activeIndex, lastIndex);
};

const buildCustomPagination = (swiper, paginationEl) => {
    paginationEl.innerHTML = '';

    swiper.slides.forEach((_, index) => {
        const bullet = document.createElement('button');
        bullet.type = 'button';
        bullet.className = 'swiper-pagination-bullet h-2 w-2 rounded-full bg-slate-300 inline-block transition-all';
        bullet.addEventListener('click', () => swiper.slideTo(index));
        paginationEl.appendChild(bullet);
    });

    setActiveBullet(paginationEl, getActiveBulletIndex(swiper));
};

export const initGallerySwiper = () => {
    const root = document.querySelector('.mwGallery');
    const paginationEl = document.querySelector('.mwGalleryPagination');

    if (!root || !paginationEl) return;

    new Swiper(root, {
        loop: false,
        spaceBetween: 14,
        slidesPerView: 1.1,
        breakpoints: {
            640: {slidesPerView: 1.2},
            1024: {slidesPerView: 1.6},
        },
        navigation: {
            nextEl: '.mwGalleryNext',
            prevEl: '.mwGalleryPrev',
        },
        on: {
            init(swiper) {
                buildCustomPagination(swiper, paginationEl);
            },
            slideChange(swiper) {
                setActiveBullet(paginationEl, getActiveBulletIndex(swiper));
            },
            transitionEnd(swiper) {
                setActiveBullet(paginationEl, getActiveBulletIndex(swiper));
            },
            reachEnd(swiper) {
                setActiveBullet(paginationEl, swiper.slides.length - 1);
            },
            fromEdge(swiper) {
                setActiveBullet(paginationEl, getActiveBulletIndex(swiper));
            },
            resize(swiper) {
                buildCustomPagination(swiper, paginationEl);
            },
        },
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initGallerySwiper();
    });
} else {
    initGallerySwiper();
}
