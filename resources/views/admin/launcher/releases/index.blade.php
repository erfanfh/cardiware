@extends('admin.layout')

@section('title', 'مدیریت آپدیت لانچر')

@section('content')
    @php($formRelease = $editingRelease)
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h1 class="text-base font-black text-slate-900">مدیریت آپدیت MW Launcher</h1>
                <p class="mt-1 text-sm leading-7 text-slate-500">
                    فایل ZIP خروجی Publish لانچر را روی هاست دانلود یا CDN آپلود کنید و اطلاعات دقیق آن را اینجا ثبت کنید.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.launcher-releases.store') }}" class="p-5">
                @csrf
                <input type="hidden" name="id" value="{{ old('id', $formRelease?->id) }}">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="md:col-span-3">
                        <x-admin.field label="نسخه" hint="مانند 1.2.0">
                            <x-admin.form.input name="version" dir="ltr" required
                                                value="{{ old('version', $formRelease?->version) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="حداقل نسخه مجاز" hint="نسخه‌های پایین‌تر مجبور به آپدیت می‌شوند.">
                            <x-admin.form.input name="minimum_supported_version" dir="ltr"
                                                value="{{ old('minimum_supported_version', $formRelease?->minimum_supported_version) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="نام فایل ZIP">
                            <x-admin.form.input name="file_name" dir="ltr" required
                                                placeholder="MWLauncher-1.2.0.zip"
                                                value="{{ old('file_name', $formRelease?->file_name) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="حجم فایل به بایت">
                            <x-admin.form.input name="size_bytes" type="number" min="1" dir="ltr" required
                                                value="{{ old('size_bytes', $formRelease?->size_bytes) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-12">
                        <x-admin.field label="لینک مستقیم HTTPS فایل">
                            <x-admin.form.input name="download_url" type="url" dir="ltr" required
                                                placeholder="https://download.example.com/MWLauncher-1.2.0.zip"
                                                value="{{ old('download_url', $formRelease?->download_url) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-12">
                        <x-admin.field label="SHA-256 فایل ZIP">
                            <x-admin.form.input name="sha256" dir="ltr" required maxlength="64"
                                                value="{{ old('sha256', $formRelease?->sha256) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-4">
                        <x-admin.field label="تاریخ انتشار" hint="اگر فعال باشد و خالی بماند، زمان فعلی ثبت می‌شود.">
                            <x-admin.form.input name="published_at" dir="ltr" placeholder="2026-08-13 12:00:00"
                                                value="{{ old('published_at', $formRelease?->published_at?->format('Y-m-d H:i:s')) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-8">
                        <x-admin.field label="توضیحات نسخه">
                            <x-admin.form.textarea name="release_notes" rows="3"
                                                   :value="old('release_notes', $formRelease?->release_notes)" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-6">
                        <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <span class="text-sm font-bold text-slate-800">انتشار این نسخه</span>
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" value="1" class="h-5 w-5"
                                   @checked(old('is_enabled', $formRelease?->is_enabled ?? false))>
                        </label>
                    </div>
                    <div class="md:col-span-6">
                        <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-red-200 bg-red-50 p-4">
                            <span class="text-sm font-bold text-red-800">آپدیت اجباری</span>
                            <input type="hidden" name="is_mandatory" value="0">
                            <input type="checkbox" name="is_mandatory" value="1" class="h-5 w-5"
                                   @checked(old('is_mandatory', $formRelease?->is_mandatory ?? false))>
                        </label>
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <x-admin.form.button type="submit" variant="dark" size="lg">
                        {{ $formRelease ? 'ذخیره تغییرات نسخه' : 'ثبت نسخه جدید' }}
                    </x-admin.form.button>
                    @if($formRelease)
                        <a href="{{ route('admin.launcher-releases.index') }}"
                           class="inline-flex items-center rounded-xl border border-slate-300 px-5 text-sm font-bold text-slate-700">
                            انصراف
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 text-sm font-black text-slate-900">نسخه‌های ثبت‌شده</div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500">
                    <tr>
                        <th class="px-4 py-3">نسخه</th><th class="px-4 py-3">حجم</th>
                        <th class="px-4 py-3">نوع</th><th class="px-4 py-3">وضعیت</th>
                        <th class="px-4 py-3">انتشار</th><th class="px-4 py-3">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($releases as $release)
                        <tr>
                            <td class="px-4 py-3 font-black" dir="ltr">{{ $release->version }}</td>
                            <td class="px-4 py-3">{{ number_format($release->size_bytes / 1048576, 1) }} MB</td>
                            <td class="px-4 py-3">{{ $release->is_mandatory ? 'اجباری' : 'اختیاری' }}</td>
                            <td class="px-4 py-3 {{ $release->is_enabled ? 'text-emerald-700' : 'text-slate-400' }}">
                                {{ $release->is_enabled ? 'فعال' : 'پیش‌نویس' }}
                            </td>
                            <td class="px-4 py-3">{{ $release->published_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.launcher-releases.index', ['edit' => $release->id]) }}"
                                       class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.launcher-releases.destroy', $release) }}"
                                          onsubmit="return confirm('این نسخه حذف شود؟')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">نسخه‌ای ثبت نشده است.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
