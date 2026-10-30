import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swiper from 'swiper';
import { Pagination, Keyboard, A11y } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

window.Alpine = Alpine;
Alpine.plugin(collapse);

// فرمت اعداد بر اساس زبان صفحه (فارسی/انگلیسی)
const pageLocale = document.documentElement.lang === 'en' ? 'en' : 'fa';
const numFmt = new Intl.NumberFormat(pageLocale === 'en' ? 'en-US' : 'fa-IR');

function initGalleries() {
    document.querySelectorAll('[data-gallery]').forEach((el) => {
        new Swiper(el, {
            modules: [Pagination, Keyboard, A11y],
            slidesPerView: 1.1,
            spaceBetween: 14,
            centeredSlides: true,
            loop: true,
            grabCursor: true,
            keyboard: { enabled: true },
            pagination: {
                el: el.querySelector('.swiper-pagination'),
                clickable: true,
            },
            breakpoints: {
                640: { slidesPerView: 2, spaceBetween: 18, centeredSlides: false },
                1024: { slidesPerView: 3, spaceBetween: 24, centeredSlides: false },
            },
        });
    });
}

function animateCount(el) {
    const target = Number.parseInt(el.dataset.count, 10) || 0;
    const duration = 1600;
    const start = performance.now();

    // setInterval (نه rAF) — در محیط‌هایی که rAF throttled است هم روان می‌ماند
    const timer = setInterval(() => {
        const p = Math.min((performance.now() - start) / duration, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = numFmt.format(Math.round(target * eased));
        if (p >= 1) clearInterval(timer);
    }, 40);
}

function initScrollObservers() {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // شمارنده‌های آمار
    const counterIO = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            animateCount(entry.target);
            counterIO.unobserve(entry.target);
        });
    }, { threshold: 0.4 });

    document.querySelectorAll('[data-count]').forEach((el) => {
        if (reduced) {
            el.textContent = numFmt.format(Number.parseInt(el.dataset.count, 10) || 0);
        } else {
            counterIO.observe(el);
        }
    });

    if (reduced) return;

    // ظهور بخش‌ها
    const revealIO = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('in');
            revealIO.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach((el) => revealIO.observe(el));
}

/* ------------------------------------------------------------------ */
/*  اسپینر ۳۶۰° — چرخش با درگ (فریم‌های تصویری از بازی واقعی)           */
/* ------------------------------------------------------------------ */

function initSpinners() {
    document.querySelectorAll('[data-spinner]').forEach((el) => {
        const frames = Number.parseInt(el.dataset.frames, 10) || 36;
        const base = el.dataset.base.replace(/\/$/, '');
        const img = el.querySelector('img');
        if (!img) return;

        const pad = (n) => String(((n % frames) + frames) % frames).padStart(2, '0');
        const url = (n) => `${base}/f${pad(n)}.png`;

        let current = 0;
        const show = (n) => {
            current = ((n % frames) + frames) % frames;
            img.src = url(current);
        };

        // پیش‌بارگذاری همه فریم‌ها
        for (let i = 0; i < frames; i++) {
            const pre = new Image();
            pre.src = url(i);
        }

        // چرخش خودکار آرام تا اولین تعامل کاربر
        let auto = true;
        let autoTimer = null;
        const stopAuto = () => {
            auto = false;
            el.dataset.state = 'user';
            if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
        };
        autoTimer = setInterval(() => {
            if (!auto) return;
            show(current + 1);
        }, 140);
        el.dataset.state = 'auto';

        // درگ با Pointer Events (ماوس + لمس)
        let dragging = false;
        let startX = 0;
        let startFrame = 0;
        el.addEventListener('pointerdown', (e) => {
            dragging = true;
            startX = e.clientX;
            startFrame = current;
            el.setPointerCapture(e.pointerId);
            el.classList.add('is-grabbing');
            stopAuto();
            e.preventDefault();
        });
        el.addEventListener('pointermove', (e) => {
            if (!dragging) return;
            const dx = e.clientX - startX;
            show(startFrame + Math.round((dx / Math.max(el.clientWidth, 1)) * frames * 1.6));
        });
        const endDrag = () => {
            dragging = false;
            el.classList.remove('is-grabbing');
        };
        el.addEventListener('pointerup', endDrag);
        el.addEventListener('pointercancel', endDrag);
        el.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') { stopAuto(); show(current + 1); }
            if (e.key === 'ArrowRight') { stopAuto(); show(current - 1); }
        });
    });
}

/* ------------------------------------------------------------------ */
/*  کارت‌های tilt سه‌بعدی (حرکت موس → چرخش + برق)                       */
/* ------------------------------------------------------------------ */

function initTilt() {
    if (!window.matchMedia('(pointer: fine)').matches) return;

    document.querySelectorAll('[data-tilt]').forEach((card) => {
        const inner = card.querySelector('[data-tilt-inner]') || card;
        const strength = Number.parseFloat(card.dataset.tilt) || 10;

        card.addEventListener('pointermove', (e) => {
            const r = card.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            inner.style.transform = `perspective(700px) rotateX(${(-py * strength).toFixed(2)}deg) rotateY(${(px * strength).toFixed(2)}deg)`;
            card.style.setProperty('--gx', `${((px + 0.5) * 100).toFixed(1)}%`);
            card.style.setProperty('--gy', `${((py + 0.5) * 100).toFixed(1)}%`);
        });
        card.addEventListener('pointerleave', () => {
            inner.style.transform = '';
        });
    });
}

/* ------------------------------------------------------------------ */
/*  افکت‌های ماوس — دنباله نوری + پخش حروف m o d d i n g w a y . i r    */
/* ------------------------------------------------------------------ */

function initCursorFx() {
    if (window.matchMedia('(pointer: coarse)').matches) return;

    // دنباله نوری ساده
    const dot = document.createElement('div');
    dot.className = 'cursor-glow';
    document.body.appendChild(dot);
    let mx = window.innerWidth / 2, my = window.innerHeight / 2;
    let dx = mx, dy = my;
    window.addEventListener('pointermove', (e) => { mx = e.clientX; my = e.clientY; });
    const trailLoop = () => {
        dx += (mx - dx) * 0.3;
        dy += (my - dy) * 0.3;
        dot.style.transform = `translate(${dx}px, ${dy}px) translate(-50%, -50%)`;
        requestAnimationFrame(trailLoop);
    };
    requestAnimationFrame(trailLoop);

    // پخش حروف آدرس سایت به سبک ماتریکس (WAAPI — مستقل از rAF)
    // تراکم بالا + سرعت کمتر: ۲۶ حرف، فاصله فشرده، حرکت آرام‌تر
    const LETTERS = 'moddingway.ir'.split('');
    window.addEventListener('pointerdown', (e) => {
        const burstCount = 26;
        for (let i = 0; i < burstCount; i++) {
            const s = document.createElement('span');
            s.className = 'fx-letter';
            s.textContent = LETTERS[i % LETTERS.length];
            const size = 10 + Math.random() * 12;
            s.style.left = `${e.clientX + (Math.random() - 0.5) * 14}px`;
            s.style.top = `${e.clientY + (Math.random() - 0.5) * 14}px`;
            s.style.fontSize = `${size}px`;
            document.body.appendChild(s);

            const angle = (Math.PI * 2 * i) / burstCount + (Math.random() - 0.5) * 0.35;
            const dist = 38 + Math.random() * 58;
            const tx = Math.cos(angle) * dist;
            const ty = Math.sin(angle) * dist * 0.8 + 26; // سقوط آرام
            const rot = Math.random() * 110 - 55;

            s.animate(
                [
                    { transform: 'translate(-50%, -50%) scale(1)', opacity: 1 },
                    { transform: `translate(calc(-50% + ${tx * 0.5}px), calc(-50% + ${ty * 0.5}px)) rotate(${rot * 0.4}deg) scale(1.1)`, opacity: 1, offset: 0.35 },
                    { transform: `translate(calc(-50% + ${tx}px), calc(-50% + ${ty}px)) rotate(${rot}deg) scale(0.7)`, opacity: 0 },
                ],
                { duration: 1150 + Math.random() * 550, easing: 'cubic-bezier(.16,.6,.24,1)' },
            ).onfinish = () => s.remove();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initGalleries();
    initScrollObservers();
    initSpinners();
    initTilt();
    initCursorFx();
    Alpine.start();

    // پیراهن سه‌بعدی — فقط در صفحاتی که هست، به‌صورت chunk جدا لود می‌شود
    if (document.querySelector('[data-kit3d]')) {
        import('./kit-viewer.js')
            .then((m) => m.default())
            .catch((err) => console.error('kit-viewer load failed:', err));
    }

});
