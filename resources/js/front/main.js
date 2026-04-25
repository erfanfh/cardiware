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
