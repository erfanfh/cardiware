@extends('admin.layout')

@section('title', 'اعلانات لانچر')

@section('content')
    @php($item = $editingNotification)
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h1 class="text-base font-black text-slate-900">مدیریت اعلانات MW Launcher</h1>
                <p class="mt-1 text-sm text-slate-500">اعلان‌های فعال در لانچر نمایش داده می‌شوند و وضعیت خوانده‌شدن برای هر کاربر جداگانه ثبت می‌شود.</p>
            </div>
            <form method="POST" action="{{ route('admin.launcher-notifications.store') }}" class="p-5">
                @csrf
                <input type="hidden" name="id" value="{{ old('id', $item?->id) }}">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="md:col-span-8">
                        <x-admin.field label="عنوان"><x-admin.form.input name="title" required maxlength="255" value="{{ old('title', $item?->title) }}" /></x-admin.field>
                    </div>
                    <div class="md:col-span-4">
                        <x-admin.field label="نوع اعلان">
                            <select name="type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm">
                                @foreach(['info' => 'اطلاع‌رسانی', 'success' => 'موفقیت', 'warning' => 'هشدار', 'important' => 'مهم'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', $item?->type ?? 'info') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-12">
                        <x-admin.field label="متن اعلان"><x-admin.form.textarea name="message" rows="4" required :value="old('message', $item?->message)" /></x-admin.field>
                    </div>
                    <div class="md:col-span-3"><x-admin.field label="متن لینک" hint="اختیاری"><x-admin.form.input name="action_text" maxlength="80" value="{{ old('action_text', $item?->action_text) }}" /></x-admin.field></div>
                    <div class="md:col-span-6"><x-admin.field label="لینک HTTPS" hint="اختیاری"><x-admin.form.input name="action_url" type="url" dir="ltr" value="{{ old('action_url', $item?->action_url) }}" /></x-admin.field></div>
                    <div class="md:col-span-3"><x-admin.field label="ترتیب"><x-admin.form.input name="sort_order" type="number" min="0" value="{{ old('sort_order', $item?->sort_order ?? 0) }}" /></x-admin.field></div>
                    <div class="md:col-span-4"><x-admin.field label="زمان انتشار" hint="خالی: زمان فعال‌سازی"><x-admin.form.input name="published_at" type="datetime-local" dir="ltr" value="{{ old('published_at', $item?->published_at?->format('Y-m-d\\TH:i')) }}" /></x-admin.field></div>
                    <div class="md:col-span-4"><x-admin.field label="زمان پایان" hint="اختیاری"><x-admin.form.input name="expires_at" type="datetime-local" dir="ltr" value="{{ old('expires_at', $item?->expires_at?->format('Y-m-d\\TH:i')) }}" /></x-admin.field></div>
                    <div class="md:col-span-4">
                        <label class="flex h-full cursor-pointer items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <span class="text-sm font-bold text-emerald-800">فعال در لانچر</span>
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" value="1" class="h-5 w-5" @checked(old('is_enabled', $item?->is_enabled ?? false))>
                        </label>
                    </div>
                </div>
                <div class="mt-5 flex gap-3">
                    <x-admin.form.button type="submit" variant="dark" size="lg">{{ $item ? 'ذخیره تغییرات' : 'ثبت اعلان' }}</x-admin.form.button>
                    @if($item)<a href="{{ route('admin.launcher-notifications.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-5 text-sm font-bold">انصراف</a>@endif
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 text-sm font-black">اعلان‌های ثبت‌شده</div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">عنوان</th><th class="px-4 py-3">نوع</th><th class="px-4 py-3">انتشار</th><th class="px-4 py-3">وضعیت</th><th class="px-4 py-3">عملیات</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($notifications as $notification)
                        <tr>
                            <td class="px-4 py-3"><div class="font-black">{{ $notification->title }}</div><div class="mt-1 max-w-xl truncate text-xs text-slate-500">{{ $notification->message }}</div></td>
                            <td class="px-4 py-3">{{ $notification->type }}</td>
                            <td class="px-4 py-3" dir="ltr">{{ $notification->published_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 {{ $notification->is_enabled ? 'text-emerald-700' : 'text-slate-400' }}">{{ $notification->is_enabled ? 'فعال' : 'غیرفعال' }}</td>
                            <td class="px-4 py-3"><div class="flex gap-2"><a href="{{ route('admin.launcher-notifications.index', ['edit' => $notification->id]) }}" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold">ویرایش</a><form method="POST" action="{{ route('admin.launcher-notifications.destroy', $notification) }}" onsubmit="return confirm('این اعلان حذف شود؟')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700">حذف</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">اعلانی ثبت نشده است.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
