<x-front.cart
    x-data="{
            hasVip: false,
            remainingDays: 23,
            progressPercent: 72
        }"
    class="overflow-hidden"
>
    <!-- Active VIP -->
    <template x-if="hasVip">
        <div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                            <span
                                class="flex h-9 w-9 [&_svg]:w-5 [&_svg]:h-5 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                @include('icons.crown-03')
                            </span>

                        <div>
                            <div class="text-base font-extrabold text-slate-900">
                                اشتراک VIP
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 text-xs text-slate-500">
                        دسترسی شما به مادها و امکانات ویژه فعال است
                    </div>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-500">اعتبار باقی‌مانده</span>
                    <span class="font-extrabold text-slate-800" x-text="remainingDays + ' روز'"></span>
                </div>

                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200">
                    <div
                        class="h-full rounded-full bg-emerald-500"
                        :style="`width: ${progressPercent}%`"
                    ></div>
                </div>

                <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500">
                    <span>تا پایان دسترسی VIP</span>
                    <span class="font-bold text-emerald-700" x-text="progressPercent + '%'"></span>
                </div>
            </div>

            <button
                type="button"
                @click="setTab('orders')"
                class="[&_svg]:w-4 [&_svg]:h-4 mt-5 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-[0.8rem] font-extrabold text-white transition hover:opacity-95"
            >
                تمدید اشتراک VIP

                @include('icons.arrow-left-02')
            </button>
        </div>
    </template>

    <!-- Inactive VIP -->
    <template x-if="!hasVip">
        <div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                            <span
                                class="flex h-9 w-9 [&_svg]:w-5 [&_svg]:h-5 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                @include('icons.crown-03')
                            </span>

                        <div>
                            <div class="text-base font-extrabold text-slate-900">
                                اشتراک VIP ندارید
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 text-xs text-slate-500">
                        مادهای ویژه و امکانات حرفه‌ای برای شما قفل هستند
                    </div>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50/70 p-4">
                <div class="text-sm font-extrabold text-slate-900">
                    یک قدم عقب‌تر از کاربران VIP هستید
                </div>

                <p class="mt-2 text-xs leading-6 text-slate-600">
                    با فعال‌سازی VIP، به مادهای ویژه، دانلودهای اختصاصی و امکاناتی دسترسی پیدا می‌کنید که برای
                    کاربران عادی در دسترس نیست.
                </p>

                <div class="mt-4 grid gap-2 text-xs font-bold text-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        دسترسی به محتوای ویژه
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        تجربه سریع‌تر و حرفه‌ای‌تر
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        مناسب برای کاربران جدی مودینگ‌وی
                    </div>
                </div>
            </div>

            <button
                type="button"
                @click="setTab('orders')"
                class="mt-5 [&_svg]:w-4 [&_svg]:h-4  inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-amber-500 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-amber-600"
            >
                فعال‌سازی اشتراک VIP

                @include('icons.arrow-left-02')
            </button>
        </div>
    </template>
</x-front.cart>
