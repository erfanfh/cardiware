<div
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-lg transition dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/30"
    x-data="{
  voted: false,
  selected: 'free',
  votes: { free: 62, patch: 41, tutorial: 27 },
  get total() { return this.votes.free + this.votes.patch + this.votes.tutorial },
  pct(key) { return Math.round((this.votes[key] / this.total) * 100) }
}">
    <div class="flex items-center justify-between">
        <div class="font-extrabold dark:text-slate-100">نظرسنجی</div>
        <span class="text-xs text-slate-500 dark:text-slate-400">۲ دقیقه</span>
    </div>
    <p class="mt-2 text-sm text-slate-600 leading-7 dark:text-slate-300">بیشتر دنبال چه چیزی هستی؟ تا صفحه اصلی رو بهتر
        بچینیم.</p>

    <!-- Form -->
    <form class="mt-3 space-y-2"
          x-show="!voted"
          x-transition.opacity
          @submit.prevent="
     // دمو: یک رای به گزینه انتخاب‌شده اضافه می‌کنیم و نتیجه را نمایش می‌دهیم
     votes[selected] = votes[selected] + 1;
     voted = true;
   ">
        <label
            class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer dark:border-slate-700 dark:hover:bg-slate-800">
            <input type="radio" name="poll" class="accent-orange-500"
                   value="free" x-model="selected" checked/>
            <span class="text-sm font-bold dark:text-slate-200">مادهای رایگان</span>
        </label>

        <label
            class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer dark:border-slate-700 dark:hover:bg-slate-800">
            <input type="radio" name="poll" class="accent-orange-500"
                   value="patch" x-model="selected"/>
            <span class="text-sm font-bold dark:text-slate-200">پچ‌های آپدیت/نقل‌وانتقال</span>
        </label>

        <label
            class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer dark:border-slate-700 dark:hover:bg-slate-800">
            <input type="radio" name="poll" class="accent-orange-500"
                   value="tutorial" x-model="selected"/>
            <span class="text-sm font-bold dark:text-slate-200">آموزش‌ها و رفع ارورها</span>
        </label>

        <button
            class="mt-2 inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 py-3 text-sm font-extrabold text-white hover:opacity-95 transition dark:bg-brand-500">
            ثبت رأی
        </button>
    </form>

    <!-- Results -->
    <div x-show="voted" x-transition.opacity class="mt-4">
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>نتایج (دمو)</span>
            <button type="button"
                    class="text-brand-700 font-bold hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200"
                    @click="voted=false">
                تغییر رأی
            </button>
        </div>

        <div class="mt-3 space-y-3">
            <!-- Free -->
            <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-700">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm font-bold text-slate-800 dark:text-slate-200">مادهای رایگان</div>
                    <div class="text-xs font-extrabold text-slate-700 dark:text-slate-300"
                         x-text="pct('free') + '%'"></div>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                    <div class="h-full bg-brand-500 transition-all duration-500"
                         :style="`width:${pct('free')}%`"></div>
                </div>
            </div>

            <!-- Patch -->
            <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-700">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm font-bold text-slate-800 dark:text-slate-200">پچ‌های آپدیت/نقل‌وانتقال</div>
                    <div class="text-xs font-extrabold text-slate-700 dark:text-slate-300"
                         x-text="pct('patch') + '%'"></div>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                    <div class="h-full bg-brand-500 transition-all duration-500"
                         :style="`width:${pct('patch')}%`"></div>
                </div>
            </div>

            <!-- Tutorial -->
            <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-700">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm font-bold text-slate-800 dark:text-slate-200">آموزش‌ها و رفع ارورها</div>
                    <div class="text-xs font-extrabold text-slate-700 dark:text-slate-300"
                         x-text="pct('tutorial') + '%'"></div>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                    <div class="h-full bg-brand-500 transition-all duration-500"
                         :style="`width:${pct('tutorial')}%`"></div>
                </div>
            </div>
        </div>

        <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">
            مجموع رأی‌ها: <span class="font-extrabold text-slate-700 dark:text-slate-200" x-text="total"></span>
        </div>
    </div>
</div>
