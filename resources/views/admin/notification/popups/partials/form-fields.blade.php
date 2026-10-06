@php
    $targetType = old('target_type', $popup?->target_type ?? 'home');
    $specific = (bool) old('specific_target', $popup?->target_id !== null);
@endphp

<div class="space-y-5">
    <x-admin.form.validation-errors/>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-black text-slate-900">{{ $popup ? 'ویرایش پاپ‌آپ' : 'پاپ‌آپ جدید' }}</h1>
        <x-admin.form.button tag="a" href="{{ route('admin.popups.index') }}" variant="secondary">بازگشت به فهرست</x-admin.form.button>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 shadow-sm sm:px-5">
        <div>
            <h2 class="text-sm font-black text-slate-800">وضعیت نمایش پاپ‌آپ</h2>
            <p class="mt-1 text-xs leading-6 text-slate-600">با فعال‌کردن، پاپ‌آپ برای مخاطبان و صفحات انتخاب‌شده قابل نمایش می‌شود.</p>
        </div>
        <div class="flex items-center gap-3 rounded-xl bg-white px-3 py-2 ring-1 ring-emerald-200">
            <span class="text-sm font-bold text-slate-700">فعال</span>
            <x-admin.form.switch name="is_active" :checked="old('is_active', $popup?->is_active ?? false)"/>
        </div>
    </div>

    <x-admin.metabox title="محتوای پاپ‌آپ">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-admin.field required label="عنوان" :error="$errors->first('title')">
                    <x-admin.form.input name="title" :value="old('title', $popup?->title)" required maxlength="180" placeholder="مثلاً پیشنهاد ویژه این هفته"/>
                </x-admin.field>
            </div>
            <div class="md:col-span-2">
                <x-admin.field label="متن" hint="متن ساده؛ برای جلوگیری از درج کد ناامن، HTML نمایش داده نمی‌شود." :error="$errors->first('body')">
                    <x-admin.form.textarea name="body" rows="5" :value="old('body', $popup?->body)" maxlength="5000"/>
                </x-admin.field>
            </div>
            <div class="md:col-span-2">
                <x-admin.field label="تصویر (اختیاری)" :error="$errors->first('image.id')">
                    <x-admin.form.media-picker mode="field" name="image" :value="[
                        'id' => old('image.id', $popup?->image_id),
                        'path' => old('image.path', $popup?->image?->url),
                    ]" layout="vertical"/>
                </x-admin.field>
            </div>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="محل و مخاطب نمایش" class="overflow-visible">
        <div x-data="{targetType: @js($targetType), specific: @js($specific)}" class="grid gap-5 md:grid-cols-2">
            <x-admin.field required label="محل نمایش" :error="$errors->first('target_type')">
                <x-admin.form.select name="target_type" x-model="targetType" :value="$targetType" :options="[
                    'all' => 'همه صفحات سایت', 'home' => 'صفحه اصلی', 'post' => 'صفحه نوشته',
                    'page' => 'صفحه برگه', 'product' => 'صفحه محصول',
                ]"/>
            </x-admin.field>
            <x-admin.field required label="مخاطب" :error="$errors->first('audience')">
                <x-admin.form.select name="audience" :value="old('audience', $popup?->audience ?? 'all')" :options="[
                    'all' => 'همه کاربران', 'guest' => 'فقط مهمانان',
                    'authenticated' => 'فقط کاربران واردشده', 'vip' => 'فقط کاربران VIP فعال',
                ]"/>
            </x-admin.field>
            <div x-show="['post','page','product'].includes(targetType)" x-cloak class="md:col-span-2 space-y-4">
                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                    <input type="hidden" name="specific_target" value="0">
                    <input type="checkbox" name="specific_target" value="1" x-model="specific" @checked($specific)>
                    فقط یک مورد مشخص
                </label>
                <p x-show="!specific" class="text-sm text-slate-500">در این حالت در تمام صفحات این نوع محتوا نمایش داده می‌شود.</p>
                <div x-show="specific && targetType === 'post'" x-cloak>
                    <x-admin.form.async-select name="target_post_id" label="نوشته" :value="old('target_post_id', $popup?->target_type === 'post' ? $popup?->target_id : null)" :search-url="route('admin.popups.targets', 'post')" :error="$errors->first('target_post_id')"/>
                </div>
                <div x-show="specific && targetType === 'page'" x-cloak>
                    <x-admin.form.async-select name="target_page_id" label="برگه" :value="old('target_page_id', $popup?->target_type === 'page' ? $popup?->target_id : null)" :search-url="route('admin.popups.targets', 'page')" :error="$errors->first('target_page_id')"/>
                </div>
                <div x-show="specific && targetType === 'product'" x-cloak>
                    <x-admin.form.async-select name="target_product_id" label="محصول" :value="old('target_product_id', $popup?->target_type === 'product' ? $popup?->target_id : null)" :search-url="route('admin.popups.targets', 'product')" :error="$errors->first('target_product_id')"/>
                </div>
            </div>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="زمان‌بندی">
        <div class="grid gap-5 md:grid-cols-3">
            <x-admin.field required label="تأخیر نمایش (ثانیه)" :error="$errors->first('delay_seconds')">
                <x-admin.form.input name="delay_seconds" type="number" min="0" max="86400" :value="old('delay_seconds', $popup?->delay_seconds ?? 5)" required/>
            </x-admin.field>
            <x-admin.field required label="بستن عادی (دقیقه)" hint="بعد از این مدت دوباره قابل نمایش است." :error="$errors->first('close_minutes')">
                <x-admin.form.input name="close_minutes" type="number" min="1" max="10080" :value="old('close_minutes', $popup?->close_minutes ?? 60)" required/>
            </x-admin.field>
            <x-admin.field required label="دیگر نشان نده (روز)" hint="پیش‌فرض: ۳۰ روز." :error="$errors->first('hide_days')">
                <x-admin.form.input name="hide_days" type="number" min="1" max="365" :value="old('hide_days', $popup?->hide_days ?? 30)" required/>
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
