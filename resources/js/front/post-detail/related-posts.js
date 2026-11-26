import Swiper from 'swiper';
import {Navigation, Pagination} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/pagination';

const initRelatedPostsSwiper = () => {
    document.querySelectorAll('.mwRelatedPosts').forEach((element) => {
        if (element.swiper) {
            element.swiper.destroy(true, true);
        }

        const section = element.closest('#related-posts');

        if (!section) {
            return;
        }

        const slidesCount = element.querySelectorAll('.swiper-slide').length;

        if (slidesCount === 0) {
            return;
        }

        new Swiper(element, {
            modules: [Navigation, Pagination],
            slidesPerView: 1.12,
            spaceBetween: 14,
            speed: 550,
            watchOverflow: true,
            navigation: {
                prevEl: section.querySelector('.mwRelatedPostsPrev'),
                nextEl: section.querySelector('.mwRelatedPostsNext'),
            },
            pagination: {
                el: section.querySelector('.mwRelatedPostsPagination'),
                clickable: true,
                renderBullet(index, className) {
                    return `<button type="button" aria-label="Go to slide ${index + 1}" class="${className} !m-0 !h-1.5 !w-1.5 !rounded-full !bg-slate-300 !opacity-100 transition-all duration-300 dark:!bg-slate-600"></button>`;
                },
            },
            breakpoints: {
                640: {
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
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRelatedPostsSwiper);
} else {
    initRelatedPostsSwiper();
}

export {initRelatedPostsSwiper};
