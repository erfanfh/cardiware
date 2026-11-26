<div
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-lg transition dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/30"
    x-data="{
        storageKey: 'Cardiware_platform_poll_voted',
        voted: false,
        selected: 'great',

        init() {
            this.voted = localStorage.getItem(this.storageKey) === '1';
        },

        submitVote() {
            localStorage.setItem(this.storageKey, '1');
            this.voted = true;
        }
    }"
>
    <div class="flex items-center justify-between">
        <div class="font-extrabold dark:text-slate-100">نظرسنجی</div>
        <span class="text-xs text-slate-500 dark:text-slate-400">۱ دقیقه</span>
    </div>

    <p class="mt-2 text-sm text-slate-600 leading-7 dark:text-slate-300">
        نظر شما درباره پلتفرم جدید Cardiware چیه؟ دوست داریم تجربه شما رو بهتر کنیم.
    </p>

    {{-- Form --}}
    <form
        class="mt-3 space-y-2"
        x-show="! voted"
        x-transition.opacity
        @submit.prevent="submitVote()"
    >
        <label
            class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer dark:border-slate-700 dark:hover:bg-slate-800">
            <input
                type="radio"
                name="platform_poll"
                class="accent-orange-500"
                value="great"
                x-model="selected"
            />
            <span class="text-sm font-bold dark:text-slate-200">
                خیلی بهتر و حرفه‌ای‌تر شده
            </span>
        </label>

        <label
            class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer dark:border-slate-700 dark:hover:bg-slate-800">
            <input
                type="radio"
                name="platform_poll"
                class="accent-orange-500"
                value="good"
                x-model="selected"
            />
            <span class="text-sm font-bold dark:text-slate-200">
                خوبه، فقط هنوز جای بهتر شدن داره
            </span>
        </label>

        <label
            class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer dark:border-slate-700 dark:hover:bg-slate-800">
            <input
                type="radio"
                name="platform_poll"
                class="accent-orange-500"
                value="old"
                x-model="selected"
            />
            <span class="text-sm font-bold dark:text-slate-200">
                نسخه قبلی برام آشناتر و راحت‌تر بود
            </span>
        </label>

        <button
            type="submit"
            class="mt-2 inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 py-3 text-sm font-extrabold text-white hover:opacity-95 transition dark:bg-brand-500">
            ثبت رأی
        </button>
    </form>

    {{-- Thank You --}}
    <div
        x-show="voted"
        x-transition.opacity
        class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
    >
        <div class="text-sm font-extrabold text-slate-800 dark:text-slate-100">
            ممنون بابت رأی شما ❤️
        </div>

        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">
            نظر شما ثبت شد و به ما کمک می‌کنه Cardiware رو بهتر و کاربردی‌تر کنیم.
        </p>
    </div>
</div>
