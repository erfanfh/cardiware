<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-base font-extrabold text-slate-900">امنیت حساب</div>
            <div class="mt-1 text-xs text-slate-500">تغییر رمز عبور حساب کاربری</div>
        </div>

        <div wire:loading wire:target="save,cancel" class="text-xs font-extrabold text-brand-700">
            در حال پردازش...
        </div>
    </div>

    @if($successMessage)
        <div
            class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold leading-7 text-emerald-800">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold leading-7 text-red-800">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="mt-5 space-y-5">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div>
                <label class="text-[11px] font-extrabold text-slate-500">رمز عبور فعلی</label>

                <input
                    type="password"
                    wire:model.defer="current_password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:bg-white focus:ring-4
                    @error('current_password') border-red-300 bg-red-50 focus:border-red-300 focus:ring-red-100 @else border-slate-200 bg-slate-50 focus:border-brand-300 focus:ring-brand-100 @enderror"
                >

                @error('current_password')
                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-[11px] font-extrabold text-slate-500">رمز عبور جدید</label>

                <input
                    type="password"
                    wire:model.defer="password"
                    autocomplete="new-password"
                    placeholder="حداقل ۸ کاراکتر"
                    class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:bg-white focus:ring-4
                    @error('password') border-red-300 bg-red-50 focus:border-red-300 focus:ring-red-100 @else border-slate-200 bg-slate-50 focus:border-brand-300 focus:ring-brand-100 @enderror"
                >

                @error('password')
                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-[11px] font-extrabold text-slate-500">تکرار رمز عبور جدید</label>

                <input
                    type="password"
                    wire:model.defer="password_confirmation"
                    autocomplete="new-password"
                    placeholder="رمز عبور جدید را دوباره وارد کنید"
                    class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:bg-white focus:ring-4
                    @error('password_confirmation') border-red-300 bg-red-50 focus:border-red-300 focus:ring-red-100 @else border-slate-200 bg-slate-50 focus:border-brand-300 focus:ring-brand-100 @enderror"
                >

                @error('password_confirmation')
                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-sm font-extrabold text-slate-900">نکته امنیتی</div>
                    <div class="mt-1 text-xs font-bold leading-6 text-slate-500">
                        برای امنیت بیشتر، رمز عبور جدید باید حداقل ۸ کاراکتر باشد و با رمز عبور فعلی متفاوت باشد.
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="cursor-pointer inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-2.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="save">ذخیره رمز جدید</span>
                        <span wire:loading wire:target="save">در حال ذخیره...</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>
