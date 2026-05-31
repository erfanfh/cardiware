<template x-if="isPhysicalProduct">
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-start">
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-amber-600 shadow-sm">
                <span class="text-lg leading-none">!</span>
            </div>

            <div>
                <div class="text-sm font-bold text-amber-800">
                    مدیریت محصول فیزیکی به زودی اضافه می‌شود.
                </div>

                <div class="mt-1 text-xs leading-6 text-amber-700">
                    تنظیمات مربوط به موجودی، انبار، ارسال، وزن، ابعاد و قیمت‌گذاری محصول فیزیکی هنوز در این نسخه
                    پیاده‌سازی نمی‌شود.
                </div>
            </div>
        </div>
    </div>
</template>
