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

    // Physical product slider is initialized in physical-products.js is initialized.
})();

/*** ==================== Team slider ==================== ***/
(function () {
    const track = document.querySelector('[data-mw-team-track]');

    if (!track) return;

    const prev = document.querySelector('[data-mw-team-prev]');
    const next = document.querySelector('[data-mw-team-next]');

    if (prev) {
        prev.addEventListener('click', () => track.scrollBy({left: 340, behavior: 'smooth'}));
    }

    if (next) {
        next.addEventListener('click', () => track.scrollBy({left: -340, behavior: 'smooth'}));
    }
})();
