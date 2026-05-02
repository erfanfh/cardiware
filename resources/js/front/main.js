import './lightbox-gallery.js';

(function () {
    const items = [
        "• نسخه جدید پچ لیگ ایران منتشر شد (FC26)",
        "• آموزش رفع ارورهای رایج نصب به‌روزرسانی شد",
        "• پک گرافیک PES 2026 با لینک‌های جدید قرار گرفت",
        "• بخش دانلودهای چندسروره فعال شد (آزمایشی)"
    ];
    const el = document.getElementById("newsTicker");
    if (!el) return;

    let i = 0;
    setInterval(() => {
        el.classList.remove("show");
        setTimeout(() => {
            i = (i + 1) % items.length;
            el.textContent = items[i];
            el.classList.add("show");
        }, 380);
    }, 2600);
})();

document.addEventListener('alpine:init', () => {
    Alpine.data('themeSwitcher', () => ({
        dark: document.documentElement.classList.contains('dark'),

        toggle() {
            this.dark = !this.dark;

            localStorage.setItem('theme', this.dark ? 'dark' : 'light');

            document.documentElement.classList.toggle('dark', this.dark);
        }
    }))
})

document.addEventListener('DOMContentLoaded', function () {
    function hideAll(selector) {
        document.querySelectorAll(selector).forEach(function (content) {
            content.style.display = 'none';
        });
    }

    function toggleBox(titleSelector, boxSelector, contentSelector) {
        document.addEventListener('click', function (event) {
            const title = event.target.closest(titleSelector);

            if (!title) {
                return;
            }

            const box = title.closest(boxSelector);

            if (!box) {
                return;
            }

            const content = box.querySelector(contentSelector);

            if (!content) {
                return;
            }

            const isHidden = window.getComputedStyle(content).display === 'none';

            content.style.display = isHidden ? 'block' : 'none';
        });
    }

    hideAll('.sh313_toggle_box_content');
    hideAll('.properties_dl_box_content');

    toggleBox('.sh313_toggle_box_title', '.sh313_toggle_box', '.sh313_toggle_box_content');
    toggleBox('.properties_dl_box_title', '.properties_dl_box', '.properties_dl_box_content');
});
