@php($plan = $plan ?? null)

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.metabox title="مشخصات پلن VIP">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <x-admin.field required label="عنوان پلن" :error="$errors->first('title')">
                <x-admin.form.input name="title" :value="old('title', $plan?->title)" required placeholder="مثلاً VIP سه‌ماهه"/>
            </x-admin.field>

            <x-admin.field required label="کد یکتای پلن" hint="شناسه داخلی پایدار برای اتصال خرید و دسترسی در مراحل بعد؛ فقط حروف کوچک انگلیسی، عدد، نقطه، خط تیره و زیرخط. پس از استفاده در خریدها تغییرش ندهید." :error="$errors->first('code')">
                <x-admin.form.input name="code" dir="ltr" :value="old('code', $plan?->code)" required placeholder="vip-90-days"/>
            </x-admin.field>

            <x-admin.field required label="مدت اعتبار (روز)" :error="$errors->first('duration_days')">
                <x-admin.form.input name="duration_days" type="number" min="1" max="36500" dir="ltr" :value="old('duration_days', $plan?->duration_days)" required/>
            </x-admin.field>

            <x-admin.field required label="قیمت (تومان)" hint="برای پلن رایگان مقدار صفر وارد کنید." :error="$errors->first('price_toman')">
                <x-admin.form.input name="price_toman" type="number" min="0" dir="ltr" :value="old('price_toman', $plan?->price_toman)" required/>
            </x-admin.field>

            <x-admin.field required label="ترتیب نمایش" hint="عدد کوچک‌تر زودتر نمایش داده می‌شود." :error="$errors->first('sort_order')">
                <x-admin.form.input name="sort_order" type="number" min="0" max="65535" dir="ltr" :value="old('sort_order', $plan?->sort_order ?? 0)" required/>
            </x-admin.field>

            <div class="flex items-center gap-3 pt-6">
                <x-admin.form.switch name="is_active" :checked="old('is_active', $plan?->is_active ?? false)"/>
                <span class="text-sm font-semibold text-slate-700">فعال و قابل ارائه بودن پلن</span>
            </div>

            <div class="md:col-span-2">
                <x-admin.field label="توضیحات" :error="$errors->first('description')">
                    <x-admin.form.textarea name="description" rows="5" :value="old('description', $plan?->description)"/>
                </x-admin.field>
            </div>
        </div>
    </x-admin.metabox>
</x-admin.page>
