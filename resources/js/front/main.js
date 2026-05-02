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
    function hideElement(element) {
        element.style.display = 'none';
    }

    function showElement(element) {
        element.style.display = '';
    }

    function toggleElement(element) {
        const currentDisplay = window.getComputedStyle(element).display;

        if (currentDisplay === 'none') {
            showElement(element);
        } else {
            hideElement(element);
        }
    }

    function getSiblingsBySelector(element, selector) {
        const parent = element.parentElement;

        if (!parent) {
            return [];
        }

        return Array.from(parent.children).filter(function (sibling) {
            return sibling !== element && sibling.matches(selector);
        });
    }

    document.querySelectorAll('.properties_dl_box_content').forEach(function (content) {
        hideElement(content);
    });

    document.addEventListener('click', function (event) {
        const shTitle = event.target.closest('.sh313_toggle_box_title');

        if (shTitle) {
            getSiblingsBySelector(shTitle, '.sh313_toggle_box_content').forEach(toggleElement);
            return;
        }

        const propertiesTitle = event.target.closest('.properties_dl_box_title');

        if (propertiesTitle) {
            getSiblingsBySelector(propertiesTitle, '.properties_dl_box_content').forEach(toggleElement);
        }
    });
});
