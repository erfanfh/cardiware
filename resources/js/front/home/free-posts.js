import Swiper from 'swiper';
import {Autoplay, Pagination} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/pagination';

function initFreeModsMainPostsSwiper() {
    document.querySelectorAll('.mwFreeModsMainPosts').forEach((element) => {
        if (element.swiper) {
            element.swiper.destroy(true, true);
        }

        new Swiper(element, {
            modules: [Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 16,
            loop: false,
            speed: 700,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: element.querySelector('.mwFreeModsMainPostsPagination'),
                clickable: true,
                renderBullet(index, className) {
                    return `
                        <span class="${className} transition-all duration-300"></span>
                    `;
                },
            },
            breakpoints: {
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 16,
                },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', initFreeModsMainPostsSwiper);
document.addEventListener('livewire:navigated', initFreeModsMainPostsSwiper);

document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morph.updated', () => {
        initFreeModsMainPostsSwiper();
    });
});
