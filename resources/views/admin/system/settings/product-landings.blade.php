@extends('admin.layout')

@section('title', 'تنظیمات لندینگ‌های PGL')

@section('content')
    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-center gap-3" x-data="{ adding: {{ $errors->has('slug') || $errors->has('label') ? 'true' : 'false' }} }">
            @foreach($labels as $key => $label)
                <a href="{{ route('admin.settings.product-landings', $key) }}"
                   class="rounded-2xl border px-4 py-3 text-sm {{ $document === $key ? 'border-orange-300 bg-orange-50 text-orange-700' : 'border-slate-200 bg-white text-slate-700' }}">
                    {{ $label }}
                </a>
            @endforeach

            <button type="button" x-on:click="adding = !adding"
                    class="cursor-pointer rounded-2xl border border-dashed border-orange-300 px-4 py-3 text-sm font-bold text-orange-600 hover:bg-orange-50">
                + لندینگ محصول جدید
            </button>

            <form x-show="adding" x-cloak method="post" action="{{ route('admin.settings.product-landings.custom.store') }}"
                  class="flex w-full flex-wrap items-end gap-3 rounded-2xl border border-orange-200 bg-orange-50/50 p-4 text-sm">
                @csrf
                <label class="flex flex-col gap-1">
                    <span class="font-bold text-slate-700">نام محصول</span>
                    <input name="label" value="{{ old('label') }}" required maxlength="80" placeholder="PGL 6"
                           class="h-10 rounded-xl border border-slate-200 bg-white px-3">
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-bold text-slate-700">نامک (آدرس)</span>
                    <input name="slug" value="{{ old('slug', 'pgl-') }}" required maxlength="60" dir="ltr" placeholder="pgl-6"
                           class="h-10 rounded-xl border border-slate-200 bg-white px-3">
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-bold text-slate-700">کپی از</span>
                    <select name="base" class="h-10 rounded-xl border border-slate-200 bg-white px-3">
                        <option value="ultimate" @selected(old('base') === 'ultimate')>{{ $bases['ultimate'] }}</option>
                        <option value="fifa23" @selected(old('base') === 'fifa23')>{{ $bases['fifa23'] }}</option>
                    </select>
                </label>
                <button type="submit" class="h-10 cursor-pointer rounded-xl bg-orange-500 px-5 font-extrabold text-white hover:bg-orange-600">ساخت لندینگ</button>
                <p class="w-full text-xs leading-6 text-slate-500">
                    همه بخش‌ها از لندینگ انتخاب‌شده کپی می‌شوند و لندینگ تا وقتی «فعال» را روشن نکنید برای کاربران نمایش داده نمی‌شود.
                    آدرس: <span dir="ltr">{{ url('/') }}/pgl-…</span> — لیگ‌ها، تیم‌ها و صفحات جزئیات مشترک هم خودکار در دسترس‌اند.
                </p>
                @error('slug')<p class="w-full text-xs font-bold text-red-600">{{ $message }}</p>@enderror
                @error('label')<p class="w-full text-xs font-bold text-red-600">{{ $message }}</p>@enderror
            </form>
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

                @if($landingSlug)
                    <a class="font-bold text-orange-600" target="_blank" rel="noopener" href="{{ url('/' . $landingSlug) }}">مشاهده لندینگ ذخیره‌شده ↗</a>
                    <span class="text-xs text-slate-400" dir="ltr">/{{ $landingSlug }}</span>
                @else
                    <p>این اطلاعات بین همه لندینگ‌های محصول مشترک است.</p>
                @endif
            </div>

            @include('admin.system.settings.partials.product-landings.node', ['node' => $contentSchema, 'model' => 'landingContent', 'depth' => 0])

            <noscript><p>برای ویرایش و ذخیره این تنظیمات، جاوااسکریپت مرورگر باید فعال باشد.</p></noscript>
            <x-admin.form.save-button/>
        </x-admin.form>

        @if($isCustom)
            <form method="post" action="{{ route('admin.settings.product-landings.custom.destroy', $document) }}" class="js-confirm mt-6"
                  data-confirm-title="حذف لندینگ محصول" data-confirm-text="این لندینگ و همه محتوای آن حذف می‌شود.">
                @csrf
                @method('delete')
                <button type="submit" class="cursor-pointer rounded-xl border border-red-200 px-4 py-2 text-xs font-extrabold text-red-600 hover:bg-red-50">
                    حذف لندینگ {{ $labels[$document] }}
                </button>
            </form>
        @endif
    </x-admin.page>
@endsection
