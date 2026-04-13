<div>
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-black text-slate-900">آیتم‌های فهرست</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        آیتم‌ها را مدیریت کنید، با drag & drop جابه‌جا کنید و برای ساختاردهی آن‌ها
                        را تو
                        در تو بچینید.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">
                                        <span x-text="countItems()"></span>
                                        <span class="mr-1">آیتم</span>
                                    </span>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-5">
            <template x-if="countItems() === 0">
                <div
                    class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm">
                        ≡
                    </div>

                    <h3 class="mt-4 text-base font-black text-slate-900">
                        هنوز آیتمی برای این فهرست ثبت نشده
                    </h3>

                    <p class="mx-auto mt-2 max-w-lg text-sm leading-7 text-slate-500">
                        از ستون سمت راست، آیتم موردنظر را انتخاب کنید تا به ساختار این فهرست اضافه
                        شود.
                    </p>
                </div>
            </template>

            <template x-if="countItems() > 0">
                <div class="nav-builder-tree space-y-4" x-ref="treeFrame">
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-bold text-slate-600">
                        برای ساختن زیرمنو، آیتم را داخل ناحیه آبی کم‌رنگ زیر آیتم مقصد رها کن. برای
                        خارج
                        کردن از زیرمنو، آیتم را به سطح بالاتر یا لیست اصلی بکش.
                    </div>

                    <div x-ref="treeRoot"></div>
                </div>
            </template>
        </div>
    </div>
</div>
