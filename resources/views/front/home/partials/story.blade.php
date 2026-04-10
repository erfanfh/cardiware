<!-- Stories + Instagram-like Viewer (Circle stories restored) -->
<section
    x-data="storyViewer()"
    @keydown.escape.window="close()"
    @keydown.arrow-right.window="next()"
    @keydown.arrow-left.window="prev()"
>
    <div class="mx-auto max-w-7xl px-4">
        <div
            class="flex items-center justify-center gap-9 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

            <!-- Story item (CIRCLE like before) -->
            <button type="button" class="group flex flex-col items-center gap-2 shrink-0 text-right"
                    @click="openByIndex(0)">
                <div class="h-16 w-16 rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px]">
                    <div class="h-full w-full rounded-full bg-white grid place-items-center">
                        <img src="https://moddingway.ir/wp-content/uploads/2025/10/FC-26-Unlock-All-1.0-TU4-300x300.jpg"
                             alt="" class="h-14 w-14 rounded-full object-cover"/>
                    </div>
                </div>
                <span class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition">پچ جدید</span>
            </button>

            <button type="button" class="group flex flex-col items-center gap-2 shrink-0" @click="openByIndex(1)">
                <div class="h-16 w-16 rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px]">
                    <div class="h-full w-full rounded-full bg-white grid place-items-center">
                        <img
                            src="https://moddingway.ir/wp-content/uploads/2025/09/%D8%A7%D8%AC%D8%B1%D8%A7-%D8%B1%D8%A7%DB%8C%DA%AF%D8%A7%D9%86-FC26-%D8%A2%D9%85%D9%88%D8%B2%D8%B4-%DA%A9%D8%B1%DA%A9-FC26-300x300.webp"
                            class="h-14 w-14 rounded-full object-cover"/>
                    </div>
                </div>
                <span
                    class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition">لیگ ایران</span>
            </button>

            <button type="button" class="group flex flex-col items-center gap-2 shrink-0" @click="openByIndex(2)">
                <div class="h-16 w-16 rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px]">
                    <div class="h-full w-full rounded-full bg-white grid place-items-center">
                        <img
                            src="https://moddingway.ir/wp-content/uploads/2025/11/FC-26-WZRD-PCK-v9-TU-1.3.0-300x300.jpg"
                            class="h-14 w-14 rounded-full object-cover"/>
                    </div>
                </div>
                <span
                    class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition">آموزش نصب</span>
            </button>

            <button type="button" class="group flex flex-col items-center gap-2 shrink-0" @click="openByIndex(3)">
                <div class="h-16 w-16 rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px]">
                    <div class="h-full w-full rounded-full bg-white grid place-items-center">
                        <img
                            src="https://moddingway.ir/wp-content/uploads/2026/01/PES-2021-Molineux-Stadium-2026-300x300.png"
                            class="h-14 w-14 rounded-full object-cover"/>
                    </div>
                </div>
                <span
                    class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition">نسخه جدید فیفا</span>
            </button>

            <button type="button" class="group flex flex-col items-center gap-2 shrink-0" @click="openByIndex(4)">
                <div class="h-16 w-16 rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px]">
                    <div class="h-full w-full rounded-full bg-white grid place-items-center">
                        <img
                            src="https://moddingway.ir/wp-content/uploads/2026/01/PES-2021-Face-Ruben-Dias-2026-By-Unknown-300x300.png"
                            class="h-14 w-14 rounded-full object-cover"/>
                    </div>
                </div>
                <span
                    class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition">لیگ اسپانیا</span>
            </button>

            <button type="button" class="group flex flex-col items-center gap-2 shrink-0" @click="openByIndex(5)">
                <div class="h-16 w-16 rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px]">
                    <div class="h-full w-full rounded-full bg-white grid place-items-center">
                        <img
                            src="https://moddingway.ir/wp-content/uploads/2026/01/PES-2017-Face-Evan-Ndicka-2026-By-Andrew-Facemaker-300x300.png"
                            class="h-14 w-14 rounded-full object-cover"/>
                    </div>
                </div>
                <span
                    class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition">پچ PGL V5</span>
            </button>

            <button type="button" class="group flex flex-col items-center gap-2 shrink-0" @click="openByIndex(6)">
                <div class="h-16 w-16 rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px]">
                    <div class="h-full w-full rounded-full bg-white grid place-items-center">
                        <img src="https://moddingway.ir/wp-content/uploads/2025/11/FC-26-BRFP-v1-TU-1.2.1-300x300.jpg"
                             class="h-14 w-14 rounded-full object-cover"/>
                    </div>
                </div>
                <span
                    class="text-xs font-semibold text-slate-600 group-hover:text-slate-800 transition">ماد آنلاکر</span>
            </button>

        </div>
    </div>

    <!-- Story Viewer Modal -->
    <div
        x-show="isOpen"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[70] flex items-center justify-center"
        style="display:none"
    >
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="close()"></div>

        <div
            class="relative w-[min(420px,92vw)] h-[min(760px,88vh)] rounded-3xl overflow-hidden shadow-2xl border border-white/10 bg-slate-900">
            <!-- progress -->
            <div class="absolute top-0 right-0 left-0 p-3 z-30">
                <div class="flex items-center gap-1.5">
                    <template x-for="(s, i) in stories" :key="i">
                        <div class="h-1.5 flex-1 rounded-full bg-white/20 overflow-hidden">
                            <div class="h-full rounded-full bg-white" :style="progressStyle(i)"></div>
                        </div>
                    </template>
                </div>

                <!-- header -->
                <div class="mt-3 flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="h-9 w-9 rounded-2xl bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px] shrink-0">
                            <div class="h-full w-full rounded-2xl bg-white grid place-items-center">
                                <img :src="current.thumb" class="h-8 w-8 rounded-xl object-cover" alt=""/>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold text-white truncate" x-text="current.title"></div>
                            <div class="text-xs text-white/70" x-text="current.meta"></div>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="h-10 w-10 rounded-2xl bg-white/10 hover:bg-white/15 transition grid place-items-center"
                        @click="close()"
                        aria-label="بستن"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-white">
                            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- image -->
            <img :src="current.image" class="absolute inset-0 h-full w-full object-cover opacity-95 select-none" alt=""
                 draggable="false"/>
            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-black/10"></div>

            <!-- bottom -->
            <div class="absolute bottom-0 right-0 left-0 p-4 z-30">
                <div class="rounded-3xl bg-white/10 ring-1 ring-white/10 backdrop-blur px-4 py-3">
                    <div class="text-sm font-extrabold text-white" x-text="current.caption"></div>
                    <div class="mt-2 flex items-center justify-between">
                        <a :href="current.href"
                           class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2 text-xs font-extrabold text-white hover:bg-brand-600 transition">
                            مشاهده جزئیات <span class="font-black">←</span>
                        </a>
                        <div class="text-xs text-white/70" x-text="`${index+1} / ${stories.length}`"></div>
                    </div>
                </div>
            </div>

            <!-- nav buttons -->
            <button type="button" class="absolute inset-y-0 left-0 w-16 sm:w-20 z-40 grid place-items-center"
                    @click.stop="prev()" aria-label="قبلی">
      <span
          class="h-11 w-11 rounded-2xl bg-white/10 ring-1 ring-white/10 backdrop-blur grid place-items-center hover:bg-white/15 transition">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-white">
          <path d="M14 7l-5 5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round"/>
        </svg>
      </span>
            </button>

            <button type="button" class="absolute inset-y-0 right-0 w-16 sm:w-20 z-40 grid place-items-center"
                    @click.stop="next()" aria-label="بعدی">
      <span
          class="h-11 w-11 rounded-2xl bg-white/10 ring-1 ring-white/10 backdrop-blur grid place-items-center hover:bg-white/15 transition">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-white">
          <path d="M10 7l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round"/>
        </svg>
      </span>
            </button>

            <!-- hold to pause -->
            <div
                class="absolute inset-0 z-20"
                @mousedown.prevent="pause()"
                @mouseup="resume()"
                @mouseleave="resume()"
                @touchstart.passive="pause()"
                @touchend.passive="resume()"
                @touchcancel.passive="resume()"
            ></div>
        </div>
    </div>
</section>

<script>
    function storyViewer() {
        return {
            isOpen: false,
            index: 0,
            durationMs: 15000,

            tick: null,
            startedAt: 0,
            elapsedBeforePause: 0,
            paused: false,

            stories: [
                {
                    title: 'پچ جدید',
                    meta: 'آپدیت ویژه • امروز',
                    thumb: 'https://moddingway.ir/wp-content/uploads/2025/10/FC-26-Unlock-All-1.0-TU4-300x300.jpg',
                    image: 'https://moddingway.ir/wp-content/uploads/2025/10/FC-26-Unlock-All-1.0-TU4-300x300.jpg',
                    caption: 'نسخه جدید منتشر شد — دانلود سریع و نصب ساده',
                    href: '#'
                },
                {
                    title: 'لیگ ایران',
                    meta: 'FC26 • راهنمای نصب',
                    thumb: 'https://moddingway.ir/wp-content/uploads/2025/09/%D8%A7%D8%AC%D8%B1%D8%A7-%D8%B1%D8%A7%DB%8C%DA%AF%D8%A7%D9%86-FC26-%D8%A2%D9%85%D9%88%D8%B2%D8%B4-%DA%A9%D8%B1%DA%A9-FC26-300x300.webp',
                    image: 'https://moddingway.ir/wp-content/uploads/2025/09/%D8%A7%D8%AC%D8%B1%D8%A7-%D8%B1%D8%A7%DB%8C%DA%AF%D8%A7%D9%86-FC26-%D8%A2%D9%85%D9%88%D8%B2%D8%B4-%DA%A9%D8%B1%DA%A9-FC26-300x300.webp',
                    caption: 'کیت‌ها، لوگوها و ترکیب‌ها — نسخه جدید',
                    href: '#'
                },
                {
                    title: 'آموزش نصب',
                    meta: 'قدم‌به‌قدم',
                    thumb: 'https://moddingway.ir/wp-content/uploads/2025/11/FC-26-WZRD-PCK-v9-TU-1.3.0-300x300.jpg',
                    image: 'https://moddingway.ir/wp-content/uploads/2025/11/FC-26-WZRD-PCK-v9-TU-1.3.0-300x300.jpg',
                    caption: 'آموزش کامل + رفع ارورهای رایج',
                    href: '#'
                },
                {
                    title: 'نسخه جدید فیفا',
                    meta: 'PES/استادیوم • 2026',
                    thumb: 'https://moddingway.ir/wp-content/uploads/2026/01/PES-2021-Molineux-Stadium-2026-300x300.png',
                    image: 'https://moddingway.ir/wp-content/uploads/2026/01/PES-2021-Molineux-Stadium-2026-300x300.png',
                    caption: 'استادیوم جدید + آپدیت محیط',
                    href: '#'
                },
                {
                    title: 'لیگ اسپانیا',
                    meta: 'Face Pack',
                    thumb: 'https://moddingway.ir/wp-content/uploads/2026/01/PES-2021-Face-Ruben-Dias-2026-By-Unknown-300x300.png',
                    image: 'https://moddingway.ir/wp-content/uploads/2026/01/PES-2021-Face-Ruben-Dias-2026-By-Unknown-300x300.png',
                    caption: 'فیس‌ها و دیتای جدید بازیکن‌ها',
                    href: '#'
                },
                {
                    title: 'پچ PGL V5',
                    meta: 'PES 2017',
                    thumb: 'https://moddingway.ir/wp-content/uploads/2026/01/PES-2017-Face-Evan-Ndicka-2026-By-Andrew-Facemaker-300x300.png',
                    image: 'https://moddingway.ir/wp-content/uploads/2026/01/PES-2017-Face-Evan-Ndicka-2026-By-Andrew-Facemaker-300x300.png',
                    caption: 'پچ محبوب + لینک‌های جدید',
                    href: '#'
                },
                {
                    title: 'ماد آنلاکر',
                    meta: 'Unlock All',
                    thumb: 'https://moddingway.ir/wp-content/uploads/2025/11/FC-26-BRFP-v1-TU-1.2.1-300x300.jpg',
                    image: 'https://moddingway.ir/wp-content/uploads/2025/11/FC-26-BRFP-v1-TU-1.2.1-300x300.jpg',
                    caption: 'باز کردن آیتم‌ها + TU جدید',
                    href: '#'
                },
            ],

            get current() {
                return this.stories[this.index] || this.stories[0];
            },

            openByIndex(i) {
                this.index = i;
                this.isOpen = true;
                this.resetTimer();
                this.startTick();
            },

            close() {
                this.isOpen = false;
                this.stopTick();
                this.resetTimer();
            },

            next() {
                if (!this.isOpen) return;
                if (this.index >= this.stories.length - 1) {
                    this.close();
                    return;
                }
                this.index++;
                this.resetTimer();
            },

            prev() {
                if (!this.isOpen) return;
                if (this.index <= 0) {
                    this.resetTimer();
                    return;
                }
                this.index--;
                this.resetTimer();
            },

            startTick() {
                this.stopTick();
                this.tick = setInterval(() => {
                    if (!this.isOpen || this.paused) return;

                    if (this.elapsed() >= this.durationMs) {
                        if (this.index >= this.stories.length - 1) this.close();
                        else {
                            this.index++;
                            this.resetTimer();
                        }
                    }
                }, 120);
            },

            stopTick() {
                if (this.tick) clearInterval(this.tick);
                this.tick = null;
            },

            resetTimer() {
                this.startedAt = Date.now();
                this.elapsedBeforePause = 0;
                this.paused = false;
            },

            pause() {
                if (!this.isOpen || this.paused) return;
                this.elapsedBeforePause = this.elapsed();
                this.paused = true;
            },

            resume() {
                if (!this.isOpen || !this.paused) return;
                this.startedAt = Date.now();
                this.paused = false;
            },

            elapsed() {
                if (!this.startedAt) return 0;
                if (this.paused) return this.elapsedBeforePause;
                return this.elapsedBeforePause + (Date.now() - this.startedAt);
            },

            progressStyle(i) {
                if (!this.isOpen) return 'width:0%';
                if (i < this.index) return 'width:100%';
                if (i > this.index) return 'width:0%';
                const p = Math.min(100, (this.elapsed() / this.durationMs) * 100);
                return `width:${p}%`;
            }
        }
    }
</script>
