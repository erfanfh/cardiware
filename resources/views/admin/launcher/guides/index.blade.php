@extends('admin.layout')

@section('title', 'راهنمای لانچر')

@section('content')
    @php($item = $editingGuide)
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h1 class="text-base font-black text-slate-900">مدیریت راهنمای MW Launcher</h1>
                <p class="mt-1 text-sm text-slate-500">مطالب فعال به ترتیب تعیین‌شده در صفحه راهنمای لانچر نمایش داده می‌شوند.</p>
            </div>
            <form method="POST" action="{{ route('admin.launcher-guides.store') }}" class="p-5">
                @csrf
                <input type="hidden" name="id" value="{{ old('id', $item?->id) }}">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="md:col-span-9"><x-admin.field label="عنوان"><x-admin.form.input name="title" required maxlength="255" value="{{ old('title', $item?->title) }}" /></x-admin.field></div>
                    <div class="md:col-span-3"><x-admin.field label="ترتیب"><x-admin.form.input name="sort_order" type="number" min="0" value="{{ old('sort_order', $item?->sort_order ?? 0) }}" /></x-admin.field></div>
                    <div class="md:col-span-12"><x-admin.field label="خلاصه" hint="اختیاری؛ حداکثر ۵۰۰ کاراکتر"><x-admin.form.textarea name="summary" rows="2" :value="old('summary', $item?->summary)" /></x-admin.field></div>
                    <div class="md:col-span-12"><x-admin.field label="متن کامل راهنما"><x-admin.form.textarea name="content" rows="12" required :value="old('content', $item?->content)" /></x-admin.field></div>
                    <div class="md:col-span-4">
                        <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <span class="text-sm font-bold text-emerald-800">نمایش در لانچر</span>
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" value="1" class="h-5 w-5" @checked(old('is_enabled', $item?->is_enabled ?? false))>
                        </label>
                    </div>
                </div>
                <div class="mt-5 flex gap-3">
                    <x-admin.form.button type="submit" variant="dark" size="lg">{{ $item ? 'ذخیره تغییرات' : 'ثبت مطلب' }}</x-admin.form.button>
                    @if($item)<a href="{{ route('admin.launcher-guides.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-5 text-sm font-bold">انصراف</a>@endif
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 text-sm font-black">مطالب راهنما</div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">عنوان</th><th class="px-4 py-3">ترتیب</th><th class="px-4 py-3">وضعیت</th><th class="px-4 py-3">عملیات</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($guides as $guide)
                        <tr>
                            <td class="px-4 py-3"><div class="font-black">{{ $guide->title }}</div><div class="mt-1 max-w-xl truncate text-xs text-slate-500">{{ $guide->summary }}</div></td>
                            <td class="px-4 py-3">{{ $guide->sort_order }}</td>
                            <td class="px-4 py-3 {{ $guide->is_enabled ? 'text-emerald-700' : 'text-slate-400' }}">{{ $guide->is_enabled ? 'فعال' : 'غیرفعال' }}</td>
                            <td class="px-4 py-3"><div class="flex gap-2"><a href="{{ route('admin.launcher-guides.index', ['edit' => $guide->id]) }}" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold">ویرایش</a><form method="POST" action="{{ route('admin.launcher-guides.destroy', $guide) }}" onsubmit="return confirm('این مطلب حذف شود؟')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700">حذف</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">مطلبی ثبت نشده است.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
