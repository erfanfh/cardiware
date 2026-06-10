<div class="lg:col-span-8 xl:col-span-9">

    <!-- DASHBOARD -->
    <div x-show="activeTab === 'dashboard'" x-transition.opacity.duration.200ms class="space-y-6">
        @include('front.account.partials.tabs.dashboard')
    </div>

    <!-- DOWNLOADS -->
    <div x-show="activeTab === 'downloads'" x-transition.opacity.duration.200ms class="space-y-6"
         style="display: none;">
        @include('front.account.partials.tabs.downloads')
    </div>

    <!-- ORDERS -->
    <div x-show="activeTab === 'orders'" x-transition.opacity.duration.200ms class="space-y-6" style="display: none;">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-base font-extrabold text-slate-900">سفارش‌های من</div>
                    <div class="mt-1 text-xs text-slate-500">تاریخچه سفارش‌ها، پرداخت‌ها و فاکتورها</div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold">
                        همه سفارش‌ها
                    </button>
                    <button
                        class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 font-extrabold hover:bg-slate-50 transition">
                        پرداخت شده
                    </button>
                    <button
                        class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 font-extrabold hover:bg-slate-50 transition">
                        در انتظار
                    </button>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-200 text-slate-500">
                        <th class="py-3 px-3 text-right font-bold">شماره سفارش</th>
                        <th class="py-3 px-3 text-right font-bold">محصول</th>
                        <th class="py-3 px-3 text-right font-bold">تاریخ</th>
                        <th class="py-3 px-3 text-right font-bold">مبلغ</th>
                        <th class="py-3 px-3 text-right font-bold">وضعیت</th>
                        <th class="py-3 px-3 text-right font-bold">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-4 px-3 font-extrabold text-slate-900">#MW-24081</td>
                        <td class="py-4 px-3 text-slate-700">پکیج VIP فصل ۲۰۲۶</td>
                        <td class="py-4 px-3 text-slate-500">۱۴۰۵/۰۱/۰۵</td>
                        <td class="py-4 px-3 text-slate-700">۳۹۹,۰۰۰ تومان</td>
                        <td class="py-4 px-3">
                            <span
                                class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-xl">پرداخت شده</span>
                        </td>
                        <td class="py-4 px-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="#"
                                   class="text-brand-700 font-extrabold hover:text-brand-800 transition">مشاهده</a>
                                <span class="text-slate-300">|</span>
                                <a href="#"
                                   class="text-brand-700 font-extrabold hover:text-brand-800 transition">فاکتور</a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="py-4 px-3 font-extrabold text-slate-900">#MW-24034</td>
                        <td class="py-4 px-3 text-slate-700">پچ لیگ ایران FC26</td>
                        <td class="py-4 px-3 text-slate-500">۱۴۰۴/۱۲/۲۸</td>
                        <td class="py-4 px-3 text-slate-700">۱۹۹,۰۰۰ تومان</td>
                        <td class="py-4 px-3">
                            <span
                                class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">تکمیل شده</span>
                        </td>
                        <td class="py-4 px-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="#"
                                   class="text-brand-700 font-extrabold hover:text-brand-800 transition">مشاهده</a>
                                <span class="text-slate-300">|</span>
                                <a href="#"
                                   class="text-brand-700 font-extrabold hover:text-brand-800 transition">دانلود</a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="py-4 px-3 font-extrabold text-slate-900">#MW-23996</td>
                        <td class="py-4 px-3 text-slate-700">مود استادیوم حرفه‌ای</td>
                        <td class="py-4 px-3 text-slate-500">۱۴۰۴/۱۲/۲۱</td>
                        <td class="py-4 px-3 text-slate-700">۱۲۰,۰۰۰ تومان</td>
                        <td class="py-4 px-3">
                            <span
                                class="text-[11px] font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded-xl">در انتظار بررسی</span>
                        </td>
                        <td class="py-4 px-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="#"
                                   class="text-brand-700 font-extrabold hover:text-brand-800 transition">پیگیری</a>
                                <span class="text-slate-300">|</span>
                                <a href="#"
                                   class="text-brand-700 font-extrabold hover:text-brand-800 transition">جزئیات</a>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- PROFILE -->
    <div x-show="activeTab === 'profile'" x-transition.opacity.duration.200ms class="space-y-6" style="display: none;">
        <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-base font-extrabold text-slate-900">اطلاعات حساب</div>
                <div class="mt-1 text-xs text-slate-500">مشخصات اصلی حساب شما</div>

                <div class="mt-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-extrabold text-slate-500">نام و نام خانوادگی</label>
                            <input type="text" value="محمد یوسف شریف"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                        </div>
                        <div>
                            <label class="text-[11px] font-extrabold text-slate-500">نام کاربری</label>
                            <input type="text" value="@m.yousuf"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-extrabold text-slate-500">ایمیل</label>
                            <input type="email" value="user@example.com"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                        </div>
                        <div>
                            <label class="text-[11px] font-extrabold text-slate-500">شماره موبایل</label>
                            <input type="text" value="0912xxx1234"
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                        </div>
                    </div>

                    <div>
                        <label class="text-[11px] font-extrabold text-slate-500">آدرس</label>
                        <textarea rows="4"
                                  class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">تهران، خیابان آزادی، بلوار دکتر قریب</textarea>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition">
                            ذخیره تغییرات
                        </button>
                        <button
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-extrabold text-brand-700 hover:bg-brand-50 hover:border-brand-200 transition">
                            انصراف
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-base font-extrabold text-slate-900">اطلاعات تکمیلی</div>
                <div class="mt-1 text-xs text-slate-500">برخی وضعیت‌های مهم حساب</div>

                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold text-slate-900">تاریخ عضویت</div>
                            <div class="text-sm font-bold text-slate-700">۱۴۰۲/۰۵/۱۲</div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold text-slate-900">نوع حساب</div>
                            <span
                                class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">VIP</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold text-slate-900">وضعیت ایمیل</div>
                            <span
                                class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-xl">تأیید شده</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold text-slate-900">وضعیت موبایل</div>
                            <span
                                class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-xl">تأیید شده</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- TICKETS -->
    <div x-show="activeTab === 'tickets'" x-transition.opacity.duration.200ms class="space-y-6" style="display: none;">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-base font-extrabold text-slate-900">تیکت‌های پشتیبانی</div>
                    <div class="mt-1 text-xs text-slate-500">درخواست‌های ثبت‌شده و پاسخ‌های پشتیبانی</div>
                </div>

                <button
                    class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition">
                    ثبت تیکت جدید
                </button>
            </div>

            <div class="mt-5 space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <div class="text-sm font-extrabold text-slate-900">مشکل در نصب پچ لیگ ایران</div>
                                <span
                                    class="text-[11px] font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded-xl">در حال بررسی</span>
                            </div>
                            <div class="mt-2 text-xs text-slate-500">کد تیکت: #TK-1091 • آخرین بروزرسانی: ۱ ساعت پیش
                            </div>
                        </div>
                        <a href="#" class="text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">مشاهده
                            گفتگو ←</a>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <div class="text-sm font-extrabold text-slate-900">درخواست بازیابی لینک دانلود</div>
                                <span
                                    class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-xl">پاسخ داده شده</span>
                            </div>
                            <div class="mt-2 text-xs text-slate-500">کد تیکت: #TK-1048 • آخرین بروزرسانی: ۲ روز پیش
                            </div>
                        </div>
                        <a href="#" class="text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">مشاهده
                            گفتگو ←</a>
                    </div>
                </div>

                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-5 text-center">
                    <div class="text-sm font-extrabold text-slate-700">نیاز به کمک داری؟</div>
                    <div class="mt-2 text-sm text-slate-500">تیکت جدید ثبت کن تا تیم پشتیبانی پاسخ بدهد.</div>
                </div>
            </div>
        </section>
    </div>

    <!-- FAVORITES -->
    <div x-show="activeTab === 'favorites'" x-transition.opacity.duration.200ms class="space-y-6"
         style="display: none;">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-base font-extrabold text-slate-900">علاقه‌مندی‌ها</div>
                    <div class="mt-1 text-xs text-slate-500">محصولاتی که برای بعد ذخیره کرده‌ای</div>
                </div>
                <span
                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">۶ مورد</span>
            </div>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-soft transition">
                    <img
                        src="https://images.unsplash.com/photo-1511882150382-421056c89033?q=80&w=600&auto=format&fit=crop"
                        alt="" class="h-40 w-full rounded-2xl object-cover">
                    <div class="mt-4 flex items-center justify-between gap-2">
                        <span
                            class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">FC</span>
                        <button
                            class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                            aria-label="حذف">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-slate-600">
                                <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-3 text-sm font-extrabold text-slate-900">فیس‌پک حرفه‌ای فصل جدید</div>
                    <div class="mt-1 text-xs text-slate-500">چهره بازیکنان با کیفیت بالا</div>
                    <div class="mt-4 flex gap-2">
                        <a href="#"
                           class="flex-1 inline-flex items-center justify-center rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition">مشاهده</a>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-soft transition">
                    <img
                        src="https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?q=80&w=600&auto=format&fit=crop"
                        alt="" class="h-40 w-full rounded-2xl object-cover">
                    <div class="mt-4 flex items-center justify-between gap-2">
                        <span
                            class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">PES</span>
                        <button
                            class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                            aria-label="حذف">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-slate-600">
                                <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round"/>
                            </svg>
                        </button>
                        </svg>
                        </button>
                    </div>
                    <div class="mt-3 text-sm font-extrabold text-slate-900">استادیوم پک اختصاصی ایران</div>
                    <div class="mt-1 text-xs text-slate-500">نور، چمن و محیط حرفه‌ای</div>
                    <div class="mt-4 flex gap-2">
                        <a href="#"
                           class="flex-1 inline-flex items-center justify-center rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition">مشاهده</a>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-soft transition">
                    <img src="https://images.unsplash.com/photo-1542751110-97427bbecf20?q=80&w=600&auto=format&fit=crop"
                         alt="" class="h-40 w-full rounded-2xl object-cover">
                    <div class="mt-4 flex items-center justify-between gap-2">
                        <span
                            class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">VIP</span>
                        <button
                            class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                            aria-label="حذف">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-slate-600">
                                <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-3 text-sm font-extrabold text-slate-900">پکیج آپدیت کامل فصل</div>
                    <div class="mt-1 text-xs text-slate-500">ترکیب چند ماد در یک پکیج</div>
                    <div class="mt-4 flex gap-2">
                        <a href="#"
                           class="flex-1 inline-flex items-center justify-center rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition">مشاهده</a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- SECURITY -->
    <div x-show="activeTab === 'security'" x-transition.opacity.duration.200ms class="space-y-6" style="display: none;">
        <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-base font-extrabold text-slate-900">امنیت حساب</div>
                <div class="mt-1 text-xs text-slate-500">تنظیمات امنیتی و رمز عبور</div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="text-[11px] font-extrabold text-slate-500">رمز عبور فعلی</label>
                        <input type="password"
                               class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                               placeholder="••••••••">
                    </div>

                    <div>
                        <label class="text-[11px] font-extrabold text-slate-500">رمز عبور جدید</label>
                        <input type="password"
                               class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                               placeholder="••••••••">
                    </div>

                    <div>
                        <label class="text-[11px] font-extrabold text-slate-500">تکرار رمز عبور جدید</label>
                        <input type="password"
                               class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                               placeholder="••••••••">
                    </div>

                    <button
                        class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition">
                        ذخیره رمز جدید
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-base font-extrabold text-slate-900">وضعیت امنیتی</div>
                <div class="mt-1 text-xs text-slate-500">بررسی سریع نشست‌ها و روش‌های ورود</div>

                <div class="mt-5 space-y-4">
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">تأیید شماره موبایل</div>
                            <div class="mt-1 text-xs text-slate-500">شماره شما با موفقیت تأیید شده است</div>
                        </div>
                        <span
                            class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-xl">فعال</span>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">ایمیل تأیید شده</div>
                            <div class="mt-1 text-xs text-slate-500">برای بازیابی سریع حساب مفید است</div>
                        </div>
                        <span
                            class="text-[11px] font-extrabold text-slate-700 bg-white border border-slate-200 px-2 py-1 rounded-xl">تأیید شده</span>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">ورود دو مرحله‌ای</div>
                            <div class="mt-1 text-xs text-slate-500">برای امنیت بیشتر حسابت را فعال کن</div>
                        </div>
                        <a href="#" class="text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">فعال‌سازی
                            ←</a>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold text-slate-900">آخرین ورود</div>
                            <div class="text-xs text-slate-500">امروز، ۱۲:۱۸</div>
                        </div>
                        <div class="mt-2 text-xs text-slate-500 leading-6">
                            دستگاه: Windows / Chrome — IP: 192.168.1.1
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold text-slate-900">نشست‌های فعال</div>
                            <button class="text-sm font-extrabold text-rose-700 hover:text-rose-800 transition">خروج از
                                همه دستگاه‌ها
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- LOGOUT -->
    <div x-show="activeTab === 'logout'" x-transition.opacity.duration.200ms class="space-y-6" style="display: none;">
        <section class="rounded-2xl border border-rose-200 bg-white p-6 shadow-sm">
            <div class="max-w-xl">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 border border-rose-200 text-rose-700">
                    ↩️
                </div>
                <h2 class="mt-4 text-xl font-black text-slate-900">خروج از حساب کاربری</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">
                    مطمئنی می‌خواهی از حساب کاربری خود خارج شوی؟ در صورت خروج، برای دسترسی به دانلودها و سفارش‌ها باید
                    دوباره وارد شوی.
                </p>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-extrabold text-white shadow-soft hover:bg-rose-700 transition">
                        بله، خارج شو
                    </a>
                    <button
                        type="button"
                        @click="setTab('dashboard')"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 hover:bg-slate-50 transition"
                    >
                        بازگشت به داشبورد
                    </button>
                </div>
            </div>
        </section>
    </div>

</div>
