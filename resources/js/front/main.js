import './lightbox-gallery.js';
import './english-display-digits.js';
import './popups.js';
import typingPlaceholder from './typing-placeholder.js';
import './old-toggle-box.js';
import './sticky-columns.js';
import './scroll-to-top.js';
import './footer-news.js';
import './career-page.js';
import './product-detail/image-gallery-slider.js';
import './product-detail/team-members.js';
import './product-detail/video-gallery.js';
import productBuyBox from './product-detail/product-buy-box.js';
import './product-list/hero-slider.js';
import productTabs from './product-detail/tabs.js';
import storyViewer from './story-viewer.js';

Alpine.data('productTabs', productTabs);
Alpine.data('storyViewer', storyViewer);

window.productBuyBox = productBuyBox;

document.addEventListener('alpine:init', () => {
    Alpine.data('typingPlaceholder', typingPlaceholder);

    Alpine.data('themeSwitcher', () => ({
        dark: document.documentElement.classList.contains('dark'),

        toggle() {
            this.dark = !this.dark;

            localStorage.setItem('theme', this.dark ? 'dark' : 'light');

            document.documentElement.classList.toggle('dark', this.dark);
        }
    }));
});

import initTimeSpentTracker from './loyalty/time-spent-tracker.js';
import initLoyaltyEarnedToast from './loyalty/earned-toast.js';
import initRecentPurchases from './recent-purchases.js';

initLoyaltyEarnedToast();
initTimeSpentTracker();
initRecentPurchases();

/*** ==================== Progress fill on view (red→green bars) ==================== ***/
(function () {
    function applyFill(fillEl) {
        const target = fillEl.getAttribute("data-fill-target");

        if (target) {
            fillEl.style.width = target;
        }

        // Tip moves with bar
        const tip = fillEl.parentElement
            ? fillEl.parentElement.querySelector(".mw-fill-tip")
            : null;

        if (tip) {
            const match = (target || "").match(/^([\d.]+)%$/);

            if (match) {
                // Tip centered on fill edge (half its width)
                const half = tip.offsetWidth ? tip.offsetWidth / 2 : 9;

                tip.style.left = `calc(${match[1]}% - ${half}px)`;
            }
        }
    }

    function initFillBars(scope) {
        const fills = scope.querySelectorAll(".mw-fill-anim:not([data-fill-done])");

        if (!fills.length) return;

        const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        if (reduced || !("IntersectionObserver" in window)) {
            fills.forEach((el) => {
                el.setAttribute("data-fill-done", "1");
                applyFill(el);
            });
            return;
        }

        // Fill element has zero width and IntersectionObserver does not see it;
        // Observe full-width parent instead.
        const pending = new Map(); // bar -> fill

        fills.forEach((el) => {
            const bar = el.closest(".mw-prj-bar, .sb-project-bar, .mw-upbox-bar") || el.parentElement;

            if (!bar) {
                el.setAttribute("data-fill-done", "1");
                applyFill(el);
                return;
            }

            pending.set(bar, el);
        });

        let stagger = 0;

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                const fillEl = pending.get(entry.target);

                io.unobserve(entry.target);
                pending.delete(entry.target);

                if (!fillEl || fillEl.hasAttribute("data-fill-done")) return;

                fillEl.setAttribute("data-fill-done", "1");

                setTimeout(() => applyFill(fillEl), 120 + (stagger++ % 6) * 140);
            });
        }, { threshold: 0.2 });

        pending.forEach((fillEl, bar) => io.observe(bar));

        // Safety net: if for any reason (background tab, unusual render) observer does not fire,
        // after loading + one second, fill all bars.
        const forceRemaining = () => {
            scope.querySelectorAll(".mw-fill-anim:not([data-fill-done])").forEach((el) => {
                el.setAttribute("data-fill-done", "1");
                applyFill(el);
            });
        };

        if (document.readyState === "complete") {
            setTimeout(forceRemaining, 1200);
        } else {
            window.addEventListener("load", () => setTimeout(forceRemaining, 1200), { once: true });
        }
    }

    function boot(scope) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => initFillBars(scope), { once: true });
        } else {
            initFillBars(scope);
        }
    }

    boot(document);

    document.addEventListener("livewire:navigated", () => initFillBars(document));
})();

/*** ==================== Entrance animations gate ==================== ***/
/* Entrance animations run only when tab is visible;
   otherwise (without JS, hidden tab, motion reduced) content stays visible. */
(function () {
    const root = document.documentElement;

    const enable = () => root.classList.add("mw-anim-ready");

    if (document.visibilityState === "visible") {
        enable();
    } else {
        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible") enable();
        }, { once: true });
    }
})();

/*** ==================== Count-up numbers (Update progress box) ==================== ***/(function () {
    function animateCount(el) {
        const to = parseFloat(el.getAttribute("data-count-to") || "0") || 0;
        const duration = 1300;
        const start = performance.now();

        function frame(now) {
            const t = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic

            el.textContent = Math.round(to * eased);

            if (t < 1) requestAnimationFrame(frame);
        }

        requestAnimationFrame(frame);
    }

    function initCounters(scope) {
        const counters = scope.querySelectorAll(".mw-count[data-count-to]:not([data-count-done])");

        if (!counters.length) return;

        const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        if (reduced || !("IntersectionObserver" in window)) {
            counters.forEach((el) => {
                el.setAttribute("data-count-done", "1");
                el.textContent = el.getAttribute("data-count-to");
            });
            return;
        }

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                const el = entry.target;

                io.unobserve(el);

                if (el.hasAttribute("data-count-done")) return;

                el.setAttribute("data-count-done", "1");

                // Synchronize with bar fill
                setTimeout(() => animateCount(el), 150);
            });
        }, { threshold: 0.4 });

        counters.forEach((el) => io.observe(el));

        // Safety net: if observer does not fire, show final value after load
        const forceRemaining = () => {
            scope.querySelectorAll(".mw-count[data-count-to]:not([data-count-done])").forEach((el) => {
                el.setAttribute("data-count-done", "1");
                el.textContent = el.getAttribute("data-count-to");
            });
        };

        if (document.readyState === "complete") {
            setTimeout(forceRemaining, 1600);
        } else {
            window.addEventListener("load", () => setTimeout(forceRemaining, 1600), { once: true });
        }
    }

    function boot(scope) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => initCounters(scope), { once: true });
        } else {
            initCounters(scope);
        }
    }

    boot(document);

    document.addEventListener("livewire:navigated", () => initCounters(document));
})();
