<div class="space-y-5">
    <x-admin.form.validation-errors/>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-black text-slate-900">{{ $story ? 'ویرایش استوری' : 'استوری جدید' }}</h1>
        @can('story.list')
            <x-admin.form.button tag="a" href="{{ route('admin.stories.index') }}" variant="secondary">بازگشت به فهرست
            </x-admin.form.button>
        @endcan
    </div>

    <div
        class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4">
        <div>
            <h2 class="text-sm font-black text-slate-800">وضعیت نمایش استوری</h2>
            <p class="mt-1 text-xs leading-6 text-slate-600">استوری فعال، در بازهٔ انتشار و در صورت فعال بودن بخش استوری
                در چیدمان صفحهٔ اصلی نمایش داده می‌شود.</p>
        </div>
        <div class="flex items-center gap-3 rounded-xl bg-white px-3 py-2 ring-1 ring-emerald-200">
            <span class="text-sm font-bold text-slate-700">فعال</span>
            <x-admin.form.switch name="is_active" :checked="old('is_active', $story?->is_active ?? false)"/>
        </div>
    </div>

    <x-admin.metabox title="محتوای استوری">
        <div class="grid gap-5 md:grid-cols-2">
            <x-admin.field required label="عنوان" :error="$errors->first('title')">
                <x-admin.form.input name="title" :value="old('title', $story?->title)" required maxlength="180"
                                    placeholder="مثلاً پچ جدید"/>
            </x-admin.field>
            <x-admin.field label="زیرعنوان" :error="$errors->first('subtitle')"
                           hint="زیر عنوان در بالای نمایشگر استوری دیده می‌شود.">
                <x-admin.form.input name="subtitle" :value="old('subtitle', $story?->subtitle)" maxlength="255"/>
            </x-admin.field>
            <x-admin.field label="بج دایره (اختیاری)" :error="$errors->first('badge')"
                           hint="مثل: ویدیو، لایو، جدید — به‌صورت قرص کوچک پایین دایره استوری نشان داده می‌شود.">
                <x-admin.form.input name="badge" :value="old('badge', $story?->badge)" maxlength="40"/>
            </x-admin.field>
            <div class="md:col-span-2">
                <x-admin.field label="توضیحات" :error="$errors->first('caption')"
                               hint="متن ساده در پایین استوری؛ حداکثر ۲۰۰۰ کاراکتر.">
                    <x-admin.form.textarea name="caption" :value="old('caption', $story?->caption)" rows="3"
                                           maxlength="2000"/>
                </x-admin.field>
            </div>
            <x-admin.field required label="تصویر اصلی" :error="$errors->first('image.id')"
                           hint="از کتابخانهٔ رسانه انتخاب یا بارگذاری کنید. تصویر عمودی با نسبت ۹:۱۶ پیشنهاد می‌شود؛ تصویر بدون برش نمایش داده خواهد شد.">
                <x-admin.form.media-picker name="image" :value="[
                    'id' => old('image.id', $story?->image_id),
                    'path' => old('image.path', $story?->image?->url),
                ]" layout="vertical" preview-height="h-48"/>
            </x-admin.field>
            <x-admin.field label="کاور دایره‌ای (اختیاری)" :error="$errors->first('thumbnail.id')"
                           hint="اگر انتخاب نشود، تصویر اصلی به عنوان کاور استفاده می‌شود. فقط تصاویر عمومی و آمادهٔ کتابخانه قابل انتخاب‌اند.">
                <x-admin.form.media-picker name="thumbnail" :value="[
                    'id' => old('thumbnail.id', $story?->thumbnail_id),
                    'path' => old('thumbnail.path', $story?->thumbnail?->url),
                ]" layout="vertical" preview-height="h-48"/>
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="لینک استوری (اختیاری)">
        <div class="grid gap-5 md:grid-cols-2">
            <x-admin.field label="آدرس لینک" :error="$errors->first('link_url')"
                           hint="آدرس کامل با http:// یا https://؛ در صورت خالی بودن، دکمه نمایش داده نمی‌شود.">
                <x-admin.form.input name="link_url" type="url" dir="ltr" :value="old('link_url', $story?->link_url)"
                                    maxlength="2048" placeholder="https://moddingway.ir/..."/>
            </x-admin.field>
            <x-admin.field label="متن دکمه" :error="$errors->first('link_label')" hint="پیش‌فرض: مشاهده جزئیات">
                <x-admin.form.input name="link_label" :value="old('link_label', $story?->link_label)" maxlength="100"/>
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="ترتیب و زمان‌بندی نمایش">
        <div class="grid gap-5 md:grid-cols-2">
            <x-admin.field required label="ترتیب نمایش" :error="$errors->first('sort_order')"
                           hint="عدد کوچک‌تر زودتر نمایش داده می‌شود؛ در ترتیب مساوی، استوری قدیمی‌تر مقدم است.">
                <x-admin.form.input name="sort_order" type="number" min="0" max="1000000"
                                    :value="old('sort_order', $story?->sort_order ?? 0)" required/>
            </x-admin.field>
            <x-admin.field required label="مدت نمایش (ثانیه)" :error="$errors->first('duration_seconds')"
                           hint="بین ۳ تا ۶۰ ثانیه">
                <x-admin.form.input name="duration_seconds" type="number" min="3" max="60"
                                    :value="old('duration_seconds', $story?->duration_seconds ?? 15)" required/>
            </x-admin.field>
            <x-admin.field label="زمان شروع" :error="$errors->first('starts_at')"
                           hint="خالی: بلافاصله پس از فعال‌سازی. منطقهٔ زمانی: {{ config('app.timezone') }}">
                <x-admin.form.date-picker name="starts_at" dir="ltr"
                                          :value="old('starts_at', jalali_date($story?->starts_at, 'Y/m/d H:i'))"/>
            </x-admin.field>
            <x-admin.field label="زمان پایان" :error="$errors->first('ends_at')"
                           hint="خالی: بدون انقضا. زمان پایان باید بعد از زمان شروع باشد.">
                <x-admin.form.date-picker name="ends_at" dir="ltr"
                                          :value="old('ends_at', jalali_date($story?->ends_at, 'Y/m/d H:i'))"/>
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
