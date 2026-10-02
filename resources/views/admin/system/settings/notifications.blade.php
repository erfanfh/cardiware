@extends('admin.layout')

@section('title', 'تنظیمات اعلان‌ها')

@section('content')
    <x-admin.page>
        <x-admin.metabox title="اعلان‌های داخل سیستم">
            <p class="mb-5 text-sm text-slate-500">هر رویداد را جداگانه فعال یا غیرفعال کنید. این تغییر فقط روی اعلان‌های بعدی اثر می‌گذارد.</p>
            <div class="space-y-3">
                @foreach($events as $key => $label)
                    @php($enabled = !isset($settings[$key]) || (bool) $settings[$key])
                    <form method="post" action="{{ route('admin.settings.notifications.update') }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="event_key" value="{{ $key }}">
                        <div>
                            <div class="font-bold">{{ $label }}</div>
                            <div class="mt-1 text-xs text-slate-500" dir="ltr">{{ $key }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <select name="is_enabled" aria-label="وضعیت {{ $label }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="1" @selected($enabled)>فعال</option>
                                <option value="0" @selected(!$enabled)>غیرفعال</option>
                            </select>
                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-bold text-white">ذخیره</button>
                        </div>
                    </form>
                @endforeach
            </div>
        </x-admin.metabox>
    </x-admin.page>
@endsection
