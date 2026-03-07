<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-soft transition"
     x-data="{
  voted: false,
  selected: 'free',
  votes: { free: 62, patch: 41, tutorial: 27 },
  get total() { return this.votes.free + this.votes.patch + this.votes.tutorial },
  pct(key) { return Math.round((this.votes[key] / this.total) * 100) }
}">
    <div class="flex items-center justify-between">
        <div class="font-extrabold">نظرسنجی</div>
        <span class="text-xs text-slate-500">۲ دقیقه</span>
    </div>
    <p class="mt-2 text-sm text-slate-600 leading-7">بیشتر دنبال چه چیزی هستی؟ تا صفحه اصلی رو بهتر بچینیم.</p>

    <!-- Form -->
    <form class="mt-3 space-y-2"
          x-show="!voted"
          x-transition.opacity
          @submit.prevent="
     // دمو: یک رای به گزینه انتخاب‌شده اضافه می‌کنیم و نتیجه را نمایش می‌دهیم
     votes[selected] = votes[selected] + 1;
     voted = true;
   ">
        <label class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer">
            <input type="radio" name="poll" class="accent-orange-500"
                   value="free" x-model="selected" checked/>
            <span class="text-sm font-bold">مادهای رایگان</span>
        </label>

        <label class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer">
            <input type="radio" name="poll" class="accent-orange-500"
                   value="patch" x-model="selected"/>
            <span class="text-sm font-bold">پچ‌های آپدیت/نقل‌وانتقال</span>
        </label>

        <label class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer">
            <input type="radio" name="poll" class="accent-orange-500"
                   value="tutorial" x-model="selected"/>
            <span class="text-sm font-bold">آموزش‌ها و رفع ارورها</span>
        </label>

        <button
            class="mt-2 inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 py-3 text-sm font-extrabold text-white hover:opacity-95 transition">
            ثبت رأی
        </button>
    </form>

    <!-- Results -->
    <div x-show="voted" x-transition.opacity class="mt-4">
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span>نتایج (دمو)</span>
            <button type="button"
                    class="text-brand-700 font-bold hover:text-brand-800"
                    @click="voted=false">
                تغییر رأی
            </button>
        </div>

        <div class="mt-3 space-y-3">
            <!-- Free -->
            <div class="rounded-2xl border border-slate-200 p-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm font-bold text-slate-800">مادهای رایگان</div>
                    <div class="text-xs font-extrabold text-slate-700" x-text="pct('free') + '%'"></div>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-brand-500 transition-all duration-500"
                         :style="`width:${pct('free')}%`"></div>
                </div>
            </div>

            <!-- Patch -->
            <div class="rounded-2xl border border-slate-200 p-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm font-bold text-slate-800">پچ‌های آپدیت/نقل‌وانتقال</div>
                    <div class="text-xs font-extrabold text-slate-700" x-text="pct('patch') + '%'"></div>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-brand-500 transition-all duration-500"
                         :style="`width:${pct('patch')}%`"></div>
                </div>
            </div>

            <!-- Tutorial -->
            <div class="rounded-2xl border border-slate-200 p-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm font-bold text-slate-800">آموزش‌ها و رفع ارورها</div>
                    <div class="text-xs font-extrabold text-slate-700" x-text="pct('tutorial') + '%'"></div>
                </div>
                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-brand-500 transition-all duration-500"
                         :style="`width:${pct('tutorial')}%`"></div>
                </div>
            </div>
        </div>

        <div class="mt-3 text-xs text-slate-500">
            مجموع رأی‌ها: <span class="font-extrabold text-slate-700" x-text="total"></span>
        </div>
    </div>
</div>
