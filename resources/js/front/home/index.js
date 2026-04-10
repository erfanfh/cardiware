import './hero-slider.js';
import './digital-products.js';
import './physical-products.js';
import './free-posts.js';

(function () {
    const tabs = Array.from(document.querySelectorAll(".free-tab"));
    const panels = Array.from(document.querySelectorAll(".free-panel"));

    function setActive(tabName) {
        tabs.forEach(btn => {
            const isActive = btn.getAttribute("data-tab") === tabName;
            btn.classList.toggle("bg-slate-900", isActive);
            btn.classList.toggle("text-white", isActive);
            btn.classList.toggle("border", !isActive);
            btn.classList.toggle("border-slate-200", !isActive);
            btn.classList.toggle("bg-white", !isActive);
            btn.classList.toggle("text-slate-800", !isActive);
        });

        panels.forEach(p => {
            const show = p.getAttribute("data-panel") === tabName;
            p.classList.toggle("hidden", !show);
        });
    }

    tabs.forEach(btn => btn.addEventListener("click", () => setActive(btn.getAttribute("data-tab"))));
    // default
    setActive("fc");

    /*** ==================== Start Physical Products ==================== ***/
    const el = document.querySelector(".physicalSwiper");
    const total = el.querySelectorAll(".swiper-slide").length;
    const mid = Math.floor(total / 2); // آیتم وسط

    const swiper = new Swiper(".physicalSwiper", {
        effect: "coverflow",
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: "auto",
        loop: true,
        spaceBetween: 30,

        // خیلی مهم: pagination رو داخل همین اسلایدر هدف بگیر (تداخل نداشته باشه)
        pagination: {
            el: ".physicalSwiper .swiper-pagination",
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
            init(sw) {
                // 👇 قطعی‌ترین روش برای شروع از آیتم وسط در حالت loop
                sw.slideToLoop(mid, 0, false);
            },
        },
    });
    /*** ==================== End Physical Products ==================== ***/
})();
