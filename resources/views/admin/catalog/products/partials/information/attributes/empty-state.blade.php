<template x-if="assignedAttributes.length === 0 && customAttributes.length === 0">
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-7 text-center">
        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
            <span class="text-lg leading-none">✦</span>
        </div>

        <div class="mt-3 text-sm font-semibold text-slate-700">
            هنوز ویژگی‌ای برای این محصول اضافه نشده است.
        </div>

        <div class="mx-auto mt-1 max-w-xl text-xs leading-6 text-slate-500">
            از ویژگی‌های عمومی انتخاب کنید یا یک ویژگی اختصاصی برای همین محصول بسازید.
        </div>
    </div>
</template>
