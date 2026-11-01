{{-- اورلی جشن هنگام دریافت موفق پاداش؛ فقط وقتی session('celebration') ست شده باشد رندر می‌شود. --}}
@if(session('celebration'))
    <div id="mw-celebration"
         x-data="{ open: true }"
         x-show="open"
         x-cloak
         x-transition.opacity.duration.300ms
         class="fixed inset-0 z-[90] grid place-items-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <canvas id="mw-confetti" class="pointer-events-none absolute inset-0 h-full w-full"></canvas>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-init="setTimeout(() => open = false, 6000)"
             class="relative w-full max-w-sm overflow-hidden rounded-3xl border border-brand-200 bg-white p-6 text-center shadow-2xl dark:border-brand-500/30 dark:bg-slate-900">
            <div class="pointer-events-none absolute -top-16 left-1/2 h-40 w-40 -translate-x-1/2 rounded-full bg-brand-400/20 blur-3xl"></div>

            <div class="relative mx-auto grid h-20 w-20 place-items-center rounded-full bg-gradient-to-br from-brand-400 to-brand-600 text-4xl shadow-lg shadow-brand-500/30">
                <span class="drop-shadow-md">🎉</span>
            </div>

            <h3 class="relative mt-4 text-lg font-black text-slate-900 dark:text-white">آفرین! پاداش فعال شد</h3>
            <p class="relative mt-2 text-xs font-bold leading-6 text-slate-500 dark:text-slate-300">
                پاداش «{{ session('celebration') }}» با موفقیت دریافت شد.
            </p>

            <button type="button"
                    @click="open = false"
                    class="relative mt-5 inline-flex h-10 w-full cursor-pointer items-center justify-center rounded-2xl bg-gradient-to-l from-brand-500 to-brand-600 px-4 text-xs font-extrabold text-white shadow-lg shadow-brand-500/25 transition hover:-translate-y-0.5">
                عالیه!
            </button>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const canvas = document.getElementById('mw-confetti');
                if (!canvas || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                const ctx = canvas.getContext('2d');
                const dpr = window.devicePixelRatio || 1;
                const resize = () => {
                    canvas.width = window.innerWidth * dpr;
                    canvas.height = window.innerHeight * dpr;
                };
                resize();

                const colors = ['#f97316', '#fbbf24', '#34d399', '#38bdf8', '#a78bfa', '#fb7185'];
                const pieces = Array.from({ length: 150 }, () => ({
                    x: Math.random(),
                    y: -0.25 - Math.random() * 0.5,
                    size: 4 + Math.random() * 5,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    vy: 0.35 + Math.random() * 0.7,
                    vx: (Math.random() - 0.5) * 0.4,
                    rot: Math.random() * Math.PI,
                    vr: (Math.random() - 0.5) * 0.25,
                }));

                let frames = 0;
                const step = () => {
                    frames += 1;
                    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                    ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);

                    pieces.forEach((p) => {
                        p.x += p.vx / 400;
                        p.y += p.vy / 160;
                        p.rot += p.vr;

                        ctx.save();
                        ctx.translate(p.x * window.innerWidth, p.y * window.innerHeight);
                        ctx.rotate(p.rot);
                        ctx.fillStyle = p.color;
                        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
                        ctx.restore();
                    });

                    if (frames < 300) {
                        requestAnimationFrame(step);
                    } else {
                        ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
                    }
                };
                requestAnimationFrame(step);
            });
        </script>
    @endpush
@endif
