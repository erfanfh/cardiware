@extends('admin.layout')

@section('title', 'تنظیمات لندینگ‌های PGL')

@section('content')
    <x-admin.page>
        <div class="mb-6 flex flex-wrap gap-3">
            @foreach($labels as $key => $label)
                <a href="{{ route('admin.settings.product-landings', $key) }}"
                   class="rounded-2xl border px-4 py-3 text-sm {{ $document === $key ? 'border-orange-300 bg-orange-50 text-orange-700' : 'border-slate-200 bg-white text-slate-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <x-admin.form action="{{ route('admin.settings.product-landings.store', $document) }}" method="put"
                      x-data="productLandingEditor({{ \Illuminate\Support\Js::from(json_decode(old('payload', json_encode($content)), true) ?? $content) }})"
                      @submit="dirty = false"
                      @beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }">
            <x-admin.form.validation-errors class="mb-6"/>
            <input type="hidden" name="version" value="{{ old('version', $version) }}">
            <input type="hidden" name="payload" :value="JSON.stringify(landingContent)">

            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 text-sm leading-7 text-slate-600">
                <p>تنظیمات {{ $labels[$document] }}؛ متن‌های فارسی و انگلیسی را جداگانه ویرایش کنید. هر بخش را برای دیدن فیلدهای آن باز کنید.</p>
                <p>مقادیر فعلی کارفرما پیش‌فرض هستند. موارد فهرست‌ها را می‌توانید اضافه، حذف یا جابه‌جا کنید. ذخیره فقط روی همین زبانه اعمال می‌شود.</p>
                @if(in_array($document, ['ultimate', 'fifa23'], true))
                    <a class="font-bold text-orange-600" target="_blank" rel="noopener" href="{{ url($document === 'ultimate' ? '/pgl-ultimate' : '/pgl-fifa-23') }}">مشاهده لندینگ ذخیره‌شده ↗</a>
                @else
                    <p>این اطلاعات بین هر دو لندینگ مشترک است.</p>
                @endif
            </div>

            @include('admin.system.settings.partials.product-landings.node', ['node' => $contentSchema, 'model' => 'landingContent', 'depth' => 0])

            <noscript><p>برای ویرایش و ذخیره این تنظیمات، جاوااسکریپت مرورگر باید فعال باشد.</p></noscript>
            <x-admin.form.save-button/>
        </x-admin.form>
    </x-admin.page>
@endsection
