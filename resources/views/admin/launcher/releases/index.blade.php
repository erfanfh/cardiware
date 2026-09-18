@extends('admin.layout')

@section('title', 'مدیریت آپدیت لانچر')

@section('content')
    @php
        $formRelease = $editingRelease;
    @endphp
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-2xl border border-orange-200 bg-white shadow-sm">
            <div class="border-b border-orange-100 px-5 py-4">
                <h1 class="text-base font-black text-slate-900">لینک دانلود عمومی MW Launcher</h1>
                <p class="mt-1 text-sm leading-7 text-slate-500">
                    این لینک در صفحه <span dir="ltr">/launcher</span> و صفحه هدایت به لانچر نمایش داده می‌شود.
                    آن را با فایل ZIP آپدیت لانچر اشتباه نگیرید؛ اینجا باید لینک نصب‌کننده عمومی کاربران ثبت شود.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.launcher-releases.public-download') }}" class="p-5">
                @csrf
                @method('PUT')
                <x-admin.field label="لینک مستقیم HTTPS نصب‌کننده لانچر"
                               hint="مثال: https://download.example.com/MWLauncher-Setup.exe">
                    <x-admin.form.input name="launcher_public_download_url" type="url" dir="ltr"
                                        placeholder="https://download.example.com/MWLauncher-Setup.exe"
                                        value="{{ old('launcher_public_download_url', $publicDownloadUrl) }}" />
                </x-admin.field>

                <div class="mt-4">
                    <x-admin.field label="حداکثر اتصال هم‌زمان هر فایل در دانلود (۱ تا ۸)"
                                   hint="هاست‌های اشتراکی با اتصال‌های زیاد سرویس را مسدود می‌کنند؛ برای dl4 مقدار ۲ یا ۳ پیشنهاد می‌شود. تغییر بدون نیاز به نسخهٔ جدید لانچر و برای دانلودهای بعدی اعمال می‌شود.">
                        <x-admin.form.input name="launcher_download_max_connections" type="number" dir="ltr"
                                            min="1" max="8"
                                            value="{{ old('launcher_download_max_connections', $downloadMaxConnections) }}" />
                    </x-admin.field>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <x-admin.form.button type="submit" variant="dark" size="lg">
                        ذخیره لینک دانلود عمومی
                    </x-admin.form.button>
                    @if(filled($publicDownloadUrl))
                        <a href="{{ $publicDownloadUrl }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700">
                            بررسی لینک فعلی
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="rounded-2xl border {{ $maintenanceEnabled ? 'border-red-300 bg-red-50/40' : 'border-slate-200 bg-white' }} shadow-sm">
            <div class="border-b {{ $maintenanceEnabled ? 'border-red-200' : 'border-slate-200' }} px-5 py-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-base font-black text-slate-900">حالت تعمیر (قطع کامل دسترسی لانچر)</h1>
                        <p class="mt-1 text-sm leading-7 text-slate-500">
                            وقتی فعال باشد، هیچ کاربری — حتی با نشست ذخیره‌شده معتبر — نمی‌تواند وارد لانچر شود؛
                            به‌جای آن همین پیام زیر را می‌بیند. این بررسی فقط موقع باز شدن لانچر یا زدن دکمه
                            «بررسی مجدد اتصال» انجام می‌شود، نه لحظه‌ای روی نشست‌های از قبل بازِ کاربران.
                        </p>
                    </div>
                    <span class="rounded-xl px-3 py-2 text-xs font-black {{ $maintenanceEnabled ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $maintenanceEnabled ? 'فعال — لانچر مسدود است' : 'غیرفعال — لانچر در دسترس است' }}
                    </span>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.launcher-releases.maintenance') }}"
                class="space-y-4 p-5"
                onsubmit="return !this.querySelector('input[type=checkbox][name=launcher_maintenance_enabled]').checked || confirm('با فعال‌کردن حالت تعمیر، همه کاربران از ورود به لانچر مسدود می‌شوند. ادامه می‌دهید؟')"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-2 block text-xs font-black text-slate-700">پیام نمایش‌داده‌شده به کاربران</label>
                    <x-admin.form.textarea name="launcher_maintenance_message" rows="2"
                                           placeholder="سرویس موقتاً در دسترس نیست."
                                           :value="old('launcher_maintenance_message', $maintenanceMessage)" />
                    @error('launcher_maintenance_message')
                        <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-red-200 bg-red-50 p-4">
                    <span class="text-sm font-black text-red-800">حالت تعمیر فعال باشد (قطع کامل ورود به لانچر)</span>
                    <input type="hidden" name="launcher_maintenance_enabled" value="0">
                    <input type="checkbox" name="launcher_maintenance_enabled" value="1" class="h-5 w-5"
                           @checked(old('launcher_maintenance_enabled', $maintenanceEnabled))>
                </label>

                <div class="flex justify-end">
                    <x-admin.form.button type="submit" variant="{{ $maintenanceEnabled ? 'dark' : 'danger' }}" size="lg">
                        ذخیره وضعیت حالت تعمیر
                    </x-admin.form.button>
                </div>
            </form>
        </section>

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
                        <x-admin.field label="نسخه" hint="مانند 1.2.0" :error="$errors->first('version')">
                            <x-admin.form.input name="version" dir="ltr" required
                                                value="{{ old('version', $formRelease?->version) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="حداقل نسخه مجاز" hint="نسخه‌های پایین‌تر مجبور به آپدیت می‌شوند."
                                       :error="$errors->first('minimum_supported_version')">
                            <x-admin.form.input name="minimum_supported_version" dir="ltr"
                                                value="{{ old('minimum_supported_version', $formRelease?->minimum_supported_version) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="نام فایل ZIP" :error="$errors->first('file_name')">
                            <x-admin.form.input name="file_name" dir="ltr" required
                                                placeholder="MWLauncher-1.2.0.zip"
                                                value="{{ old('file_name', $formRelease?->file_name) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="حجم فایل به بایت" :error="$errors->first('size_bytes')">
                            <x-admin.form.input name="size_bytes" type="number" min="1" dir="ltr" required
                                                value="{{ old('size_bytes', $formRelease?->size_bytes) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-12">
                        <x-admin.field label="لینک مستقیم HTTPS فایل" :error="$errors->first('download_url')">
                            <x-admin.form.input name="download_url" type="url" dir="ltr" required
                                                placeholder="https://download.example.com/MWLauncher-1.2.0.zip"
                                                value="{{ old('download_url', $formRelease?->download_url) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-12">
                        <x-admin.field label="SHA-256 فایل ZIP" :error="$errors->first('sha256')">
                            <x-admin.form.input name="sha256" dir="ltr" required maxlength="64"
                                                value="{{ old('sha256', $formRelease?->sha256) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-8">
                        <x-admin.field label="امضای مستقل بسته (Base64)"
                                       hint="خروجی Signature ابزار publish-update؛ برای انتشار امن هر دو فیلد امضا را ثبت کنید."
                                       :error="$errors->first('signature')">
                            <x-admin.form.textarea name="signature" rows="3" dir="ltr"
                                                   :value="old('signature', $formRelease?->signature)" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-4">
                        <x-admin.field label="شناسه کلید امضا" hint="Signature key id خروجی ابزار ساخت بسته"
                                       :error="$errors->first('signature_key_id')">
                            <x-admin.form.input name="signature_key_id" dir="ltr" maxlength="64"
                                                value="{{ old('signature_key_id', $formRelease?->signature_key_id) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-4">
                        <x-admin.field label="تاریخ انتشار" hint="اگر فعال باشد و خالی بماند، زمان فعلی ثبت می‌شود."
                                       :error="$errors->first('published_at')">
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
                            <td class="px-4 py-3">
                                {{ $release->is_mandatory ? 'اجباری' : 'اختیاری' }}
                                <span class="mr-2 {{ filled($release->signature) ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ filled($release->signature) ? '• امضاشده' : '• بدون امضا' }}
                                </span>
                            </td>
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
