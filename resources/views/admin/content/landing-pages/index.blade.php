@extends('admin.layout')

@section('title', 'لندینگ‌ساز')

@section('content')
    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="max-w-3xl text-sm leading-7 text-slate-500">
                هر لندینگی که لازم دارید (کمپین، جشنواره، معرفی پچ یا ماد، تخفیف ویژه) را با تم و رنگ دلخواه و بلوک‌های آماده بسازید.
                آدرس هر لندینگ: <span dir="ltr" class="font-mono">/landing/نامک</span>
            </p>
            <a href="{{ route('admin.landing-pages.create') }}" class="inline-flex h-10 items-center rounded-xl bg-brand-500 px-4 text-xs font-extrabold text-white hover:bg-brand-600">+ لندینگ جدید</a>
        </div>

        <x-admin.metabox title="لندینگ‌ها">
            @if($pages->isEmpty())
                <p class="text-sm text-slate-500">هنوز لندینگی ساخته نشده است.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                            <th class="px-3 py-3">عنوان</th><th class="px-3 py-3">آدرس</th><th class="px-3 py-3">وضعیت</th>
                            <th class="px-3 py-3">تم</th><th class="px-3 py-3">بازدید</th><th class="px-3 py-3"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pages as $page)
                            @php($resolved = $page->resolvedTheme())
                            <tr class="border-b border-slate-100">
                                <td class="px-3 py-2.5 font-bold"><a href="{{ route('admin.landing-pages.edit', $page) }}" class="text-brand-600">{{ $page->title }}</a></td>
                                <td class="px-3 py-2.5 font-mono text-xs" dir="ltr">/landing/{{ $page->slug }}</td>
                                <td class="px-3 py-2.5 text-xs">
                                    @if($page->isPubliclyVisible())
                                        <span class="rounded-lg bg-emerald-50 px-2 py-1 font-bold text-emerald-700">منتشرشده</span>
                                    @elseif($page->status === 'published')
                                        <span class="rounded-lg bg-amber-50 px-2 py-1 font-bold text-amber-700">بیرون از بازه نمایش</span>
                                    @else
                                        <span class="rounded-lg bg-slate-100 px-2 py-1 font-bold text-slate-600">پیش‌نویس</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="h-4 w-4 rounded-full" style="background: {{ $resolved['primary'] }}"></span>
                                        <span class="h-4 w-4 rounded-full" style="background: {{ $resolved['accent'] }}"></span>
                                        <span class="text-xs text-slate-500">{{ \App\Domain\Content\Models\LandingPage::THEME_PRESETS[$resolved['preset']]['label'] }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-2.5">{{ number_format($page->views_count) }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="flex flex-wrap gap-1.5">
                                        <a href="{{ route('landing-pages.show', ['landingSlug' => $page->slug, 'preview' => 1]) }}" target="_blank" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-bold">پیش‌نمایش</a>
                                        <form method="post" action="{{ route('admin.landing-pages.duplicate', $page) }}">@csrf<button class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-bold">کپی</button></form>
                                        <form method="post" action="{{ route('admin.landing-pages.destroy', $page) }}" class="js-confirm" data-confirm-title="حذف لندینگ" data-confirm-text="این لندینگ حذف می‌شود و آدرس آن دیگر باز نمی‌شود.">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-bold text-red-600">حذف</button></form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $pages->links() }}</div>
            @endif
        </x-admin.metabox>
    </x-admin.page>
@endsection
