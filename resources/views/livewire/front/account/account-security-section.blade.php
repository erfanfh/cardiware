<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="text-sm font-extrabold text-slate-900 sm:text-base">امنیت حساب</div>
            <div class="mt-1 text-xs leading-5 text-slate-500">تغییر رمز عبور حساب کاربری</div>
        </div>

        <div wire:loading wire:target="save,cancel" class="text-xs font-extrabold text-brand-700">
            در حال پردازش...
        </div>
    </div>

    @if($successMessage)
        <div
            class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold leading-6 text-emerald-800 sm:mt-5 sm:p-4 sm:text-sm sm:leading-7">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div
            class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-3 text-xs font-bold leading-6 text-red-800 sm:mt-5 sm:p-4 sm:text-sm sm:leading-7">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="mt-4 space-y-4 sm:mt-5 sm:space-y-5">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="min-w-0">
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
                <div class="mt-2 text-xs font-bold leading-5 text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="min-w-0">
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
                <div class="mt-2 text-xs font-bold leading-5 text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="min-w-0">
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
                <div class="mt-2 text-xs font-bold leading-5 text-red-600">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="min-w-0">
                    <div class="text-sm font-extrabold text-slate-900">نکته امنیتی</div>
                    <div class="mt-1 text-xs font-bold leading-6 text-slate-500">
                        برای امنیت بیشتر، رمز عبور جدید باید حداقل ۸ کاراکتر باشد و با رمز عبور فعلی متفاوت باشد.
                    </div>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 md:w-auto"
                >
                    <span wire:loading.remove wire:target="save">ذخیره رمز جدید</span>
                    <span wire:loading wire:target="save">در حال ذخیره...</span>
                </button>
            </div>
        </div>
    </form>
</section>
