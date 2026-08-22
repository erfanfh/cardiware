@php
    use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;

    $canClaim = $winner->status === DrawWinnerStatusEnum::PENDING
        && $winner->isEffectivePrimary()
        && (!$winner->confirmation_deadline_at || $winner->confirmation_deadline_at->isFuture());
    $user = auth()->user();
@endphp

@if($canClaim)
    <div class="mt-4 border-t border-amber-200 pt-4 dark:border-amber-500/20" x-data="{ claimOpen: {{ $errors->any() ? 'true' : 'false' }}, declineOpen: false }">
        <div class="rounded-2xl bg-amber-100/70 p-3 text-[11px] font-bold leading-6 text-amber-900 dark:bg-amber-500/10 dark:text-amber-200">
            برای حفظ جایزه، دریافت آن را پیش از پایان مهلت تأیید کن. پس از انصراف یا پایان مهلت، جایزه به نفر ذخیره می‌رسد.
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button" @click="claimOpen = !claimOpen; declineOpen = false" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-extrabold text-white transition hover:bg-emerald-700">
                تأیید و ثبت اطلاعات تحویل
            </button>
            <button type="button" @click="declineOpen = !declineOpen; claimOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                انصراف از جایزه
            </button>
        </div>

        <form x-show="claimOpen" x-cloak method="post" action="{{ route('account.raffles.winners.claim', $winner) }}" class="mt-4 grid grid-cols-1 gap-3 rounded-2xl border border-emerald-200 bg-white p-4 dark:border-emerald-500/20 dark:bg-slate-900 sm:grid-cols-2">
            @csrf
            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                نام تحویل‌گیرنده
                <input name="contact_name" value="{{ old('contact_name', $user?->display_name ?: $user?->full_name) }}" required maxlength="150" class="mt-1.5 h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800">
            </label>
            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                شماره موبایل
                <input name="mobile" value="{{ old('mobile', $user?->mobile) }}" required dir="ltr" inputmode="tel" class="mt-1.5 h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800">
            </label>
            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                کد پستی (اختیاری)
                <input name="postal_code" value="{{ old('postal_code') }}" dir="ltr" inputmode="numeric" maxlength="10" class="mt-1.5 h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800">
            </label>
            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 sm:col-span-2">
                نشانی کامل تحویل
                <textarea name="address" required maxlength="1000" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs leading-6 outline-none focus:border-brand-400 dark:border-slate-700 dark:bg-slate-800">{{ old('address') }}</textarea>
            </label>
            <label class="flex items-start gap-2 text-[11px] leading-6 text-slate-500 sm:col-span-2">
                <input type="checkbox" name="accept_rules" value="1" required class="mt-1 rounded border-slate-300 text-brand-500">
                اطلاعات واردشده صحیح است و قوانین دریافت جایزه این قرعه‌کشی را می‌پذیرم.
            </label>
            @if($errors->any())
                <div class="rounded-xl bg-red-50 p-3 text-[11px] text-red-700 sm:col-span-2">
                    {{ $errors->first() }}
                </div>
            @endif
            <div class="sm:col-span-2">
                <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-extrabold text-white hover:bg-emerald-700">ثبت قطعی دریافت جایزه</button>
            </div>
        </form>

        <form x-show="declineOpen" x-cloak method="post" action="{{ route('account.raffles.winners.decline', $winner) }}" class="mt-4 rounded-2xl border border-red-200 bg-white p-4 dark:border-red-500/20 dark:bg-slate-900">
            @csrf
            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">
                دلیل انصراف (اختیاری)
                <textarea name="reason" maxlength="500" rows="2" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs outline-none focus:border-red-400 dark:border-slate-700 dark:bg-slate-800"></textarea>
            </label>
            <label class="mt-3 flex items-start gap-2 text-[11px] leading-6 text-red-700 dark:text-red-300">
                <input type="checkbox" name="confirm_decline" value="1" required class="mt-1 rounded border-slate-300 text-red-500">
                می‌دانم این اقدام برگشت‌پذیر نیست و جایزه به نفر ذخیره منتقل می‌شود.
            </label>
            <button type="submit" class="mt-3 rounded-xl bg-red-600 px-5 py-2.5 text-xs font-extrabold text-white hover:bg-red-700">ثبت انصراف</button>
        </form>
    </div>
@elseif($winner->status === DrawWinnerStatusEnum::CONFIRMED)
    <div class="mt-4 rounded-2xl bg-sky-50 p-3 text-[11px] font-bold leading-6 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">
        دریافت جایزه تأیید شده و اطلاعات تحویل در اختیار تیم پشتیبانی است.
    </div>
@elseif($winner->status === DrawWinnerStatusEnum::DELIVERED)
    <div class="mt-4 rounded-2xl bg-emerald-50 p-3 text-[11px] font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">جایزه تحویل شده است.</div>
@endif
