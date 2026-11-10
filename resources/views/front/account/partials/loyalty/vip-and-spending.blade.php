{{-- کارت VIP (پاداش امتیاز + پروفایل اختصاصی) و تفکیک امتیازهای مصرف‌شده --}}
@php
    $vip = $club['vip'];
    $themes = \App\Application\Loyalty\Support\VipClubProfile::THEMES;
    $spendTotal = array_sum(array_column($club['spendBreakdown'], 'points'));
    $spendIcons = ['coupon' => '🎟️', 'raffle' => '🎲', 'wallet' => '👛', 'reward' => '🎁', 'other' => '•'];
@endphp

<div class="grid gap-4 lg:grid-cols-2">
    {{-- ===== VIP ===== --}}
    <section class="relative overflow-hidden rounded-2xl border {{ $vip['isVip'] ? 'border-amber-300 dark:border-amber-500/40' : 'border-slate-200 dark:border-slate-700' }} bg-white p-5 shadow-sm dark:bg-slate-900">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-1.5 bg-gradient-to-l from-amber-300 via-amber-500 to-orange-500"></div>

        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-[11px] font-extrabold text-amber-600 dark:text-amber-400">👑 مزایای VIP در باشگاه</div>
                <h3 class="mt-1 text-base font-black text-slate-900 dark:text-white">
                    {{ $vip['isVip'] ? 'شما عضو VIP هستید' : 'با VIP سریع‌تر امتیاز بگیرید' }}
                </h3>
                @if($vip['isVip'] && $vip['since'])
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">VIP از {{ jalali_date($vip['since'], 'j F Y') }}</p>
                @endif
            </div>
            @if($vip['bonusPercent'] > 0)
                <span class="shrink-0 rounded-xl bg-amber-50 px-3 py-1.5 text-sm font-black text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">+{{ number_format($vip['bonusPercent']) }}٪ امتیاز</span>
            @endif
        </div>

        <ul class="mt-3 space-y-1.5 text-xs leading-6 text-slate-600 dark:text-slate-300">
            @if($vip['bonusPercent'] > 0)
                <li>⚡ هر امتیازی که می‌گیرید {{ number_format($vip['bonusPercent']) }}٪ بیشتر حساب می‌شود (خرید، دیدگاه، بازدید و …).</li>
            @endif
            <li>🏅 نشان VIP کنار نام شما در دیدگاه‌ها، جدول برترین‌ها و کارت باشگاه برای همه نمایش داده می‌شود.</li>
            <li>🎨 کارت عمومی باشگاه اختصاصی: تم رنگی دلخواه و معرفی کوتاه.</li>
            <li>📦 دسترسی به محصولات VIP بدون خرید جداگانه.</li>
        </ul>

        @if($vip['isVip'])
            @php
                $bioStatus = (string)($user->club_bio_status ?? 'none');
                $canEditBio = $user->canEditClubBio();
            @endphp
            <div class="mt-3 rounded-xl bg-amber-50/70 px-3 py-2 text-xs font-bold text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                امتیاز اضافه‌ای که تا امروز به‌خاطر VIP گرفته‌اید: {{ number_format($vip['bonusEarned']) }}
            </div>

            <form method="post" action="{{ route('account.loyalty.vip-profile.update') }}" class="mt-4 space-y-3 border-t border-slate-100 pt-4 dark:border-slate-800"
                  x-data="{ bio: @js(old('club_bio', $vip['bio'] ?? '')), theme: @js(old('club_theme', $vip['theme'])) }">
                @csrf
                @method('put')
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-800 dark:text-slate-100">پروفایل اختصاصی شما</span>
                    <a href="{{ route('loyalty.members.show', $user) }}" target="_blank" class="text-[11px] font-bold text-brand-600 hover:underline dark:text-brand-400">مشاهده کارت عمومی ↗</a>
                </div>

                {{-- وضعیت معرفی: تایید/رد/در انتظار --}}
                @if($bioStatus === 'pending')
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-[11px] font-bold leading-6 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                        ⏳ معرفی شما در انتظار تایید مدیر است. تا تعیین تکلیف، معرفی و تم قابل تغییر نیستند و متن روی کارت عمومی نمایش داده نمی‌شود.
                    </div>
                @elseif($bioStatus === 'approved')
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-[11px] font-bold leading-6 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                        ✅ معرفی شما تایید شده و روی کارت عمومی نمایش داده می‌شود.
                        @if(! $canEditBio)
                            برای ویرایش بعدی، اشتراک ماهِ بعدِ آخرین ویرایش را تهیه کنید (ماهی یک‌بار).
                        @endif
                    </div>
                @elseif($bioStatus === 'rejected')
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-[11px] font-bold leading-6 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                        ⚠️ معرفی قبلی شما تایید نشد. می‌توانید یک‌بار دیگر متن جدیدی بنویسید.
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] font-bold leading-6 text-slate-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                        معرفی شما پس از ثبت، پیش از نمایش عمومی توسط مدیر بررسی می‌شود. نوشتن فقط یک‌بار امکان‌پذیر است؛ ویرایش بعدی با خرید اشتراک ماهِ بعد.
                    </div>
                @endif

                <fieldset @disabled(! $canEditBio) @class(['opacity-60' => ! $canEditBio])>
                    <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="تم کارت">
                        @foreach($themes as $key => $theme)
                            <label class="cursor-pointer">
                                <input type="radio" name="club_theme" value="{{ $key }}" x-model="theme" class="peer sr-only">
                                <span class="flex items-center gap-1.5 rounded-xl border border-slate-200 px-2.5 py-1.5 text-[11px] font-bold text-slate-600 transition peer-checked:border-slate-900 peer-checked:ring-2 peer-checked:ring-slate-900/10 dark:border-slate-700 dark:text-slate-300 dark:peer-checked:border-white">
                                    <span class="h-4 w-4 rounded-full bg-gradient-to-l {{ $theme['header'] }}"></span>
                                    {{ $theme['label'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <label class="mt-3 block">
                        <span class="sr-only">معرفی کوتاه</span>
                        <textarea name="club_bio" rows="2" maxlength="{{ \App\Application\Loyalty\Support\VipClubProfile::BIO_MAX }}" x-model="bio"
                                  placeholder="یک جمله درباره خودت؛ مثلاً «طرفدار پرسپولیس و عاشق مادهای لیگ برتر»"
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs leading-6 text-slate-700 outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100 disabled:cursor-not-allowed dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:ring-amber-500/10"></textarea>
                        <span class="mt-1 flex justify-between text-[10px] text-slate-400">
                            <span>لینک، ایمیل و شماره تماس مجاز نیست.</span>
                            <span x-text="`${bio.length} / {{ \App\Application\Loyalty\Support\VipClubProfile::BIO_MAX }}`"></span>
                        </span>
                    </label>
                </fieldset>
                @error('club_bio')<p class="text-xs font-bold text-rose-600">{{ $message }}</p>@enderror
                @error('club_theme')<p class="text-xs font-bold text-rose-600">{{ $message }}</p>@enderror

                @if($canEditBio)
                    <button type="submit" class="inline-flex h-9 cursor-pointer items-center rounded-xl bg-amber-500 px-4 text-xs font-black text-white transition hover:bg-amber-600">ذخیره و ارسال برای تایید</button>
                @endif
            </form>
        @else
            <a href="{{ route('vip.index') }}" class="mt-4 inline-flex h-9 items-center rounded-xl bg-amber-500 px-4 text-xs font-black text-white transition hover:bg-amber-600">مشاهده پلن‌های VIP</a>
        @endif
    </section>

    {{-- ===== Spending breakdown ===== --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-[11px] font-extrabold text-cyan-600 dark:text-cyan-400">گزارش امتیاز</div>
                <h3 class="mt-1 text-base font-black text-slate-900 dark:text-white">امتیازهایتان کجا رفت؟</h3>
            </div>
            <a href="{{ route('account.loyalty', ['points_filter' => 'spent']) }}#point-history" class="shrink-0 text-[11px] font-bold text-brand-600 hover:underline dark:text-brand-400">جزئیات مصرف‌ها</a>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-xl bg-emerald-50 p-2.5 dark:bg-emerald-500/10">
                <div class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300">کل کسب‌شده</div>
                <div class="mt-1 text-sm font-black text-emerald-700 dark:text-emerald-300">{{ number_format($club['lifetimeEarned']) }}</div>
            </div>
            <div class="rounded-xl bg-rose-50 p-2.5 dark:bg-rose-500/10">
                <div class="text-[10px] font-bold text-rose-700 dark:text-rose-300">کل مصرف‌شده</div>
                <div class="mt-1 text-sm font-black text-rose-700 dark:text-rose-300">{{ number_format($club['lifetimeSpent']) }}</div>
            </div>
            <div class="rounded-xl bg-brand-50 p-2.5 dark:bg-brand-500/10">
                <div class="text-[10px] font-bold text-brand-700 dark:text-brand-300">قابل استفاده</div>
                <div class="mt-1 text-sm font-black text-brand-700 dark:text-brand-300">{{ number_format($club['spendablePointBalance']) }}</div>
            </div>
        </div>

        @if($club['spendBreakdown'] === [])
            <p class="mt-4 rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                هنوز امتیازی مصرف نکرده‌اید. از تب «پاداش‌ها» می‌توانید کد تخفیف یا کد قرعه‌کشی بگیرید.
            </p>
        @else
            <ul class="mt-4 space-y-2.5">
                @foreach($club['spendBreakdown'] as $row)
                    @php($share = $spendTotal > 0 ? (int) round($row['points'] / $spendTotal * 100) : 0)
                    <li>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $spendIcons[$row['key']] ?? '•' }} {{ $row['label'] }}
                                <span class="font-normal text-slate-400">({{ number_format($row['count']) }} بار)</span></span>
                            <span class="font-black text-slate-900 dark:text-white">{{ number_format($row['points']) }}</span>
                        </div>
                        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-gradient-to-l from-cyan-400 to-brand-500" style="width: {{ max(3, $share) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-[10px] leading-5 text-slate-400">مصرف‌هایی که برگشت خورده‌اند (مثل پاداش لغوشده) در این گزارش حساب نمی‌شوند.</p>
        @endif
    </section>
</div>
