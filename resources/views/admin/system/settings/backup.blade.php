@extends('admin.layout')

@section('title', 'بک‌آپ خودکار')

@section('content')
    @php
        $defaults = \App\Infrastructure\Services\Setting\Groups\BackupSettings::defaults();
        $get = fn (string $key) => old($key, get_setting('backup', $key, $defaults[$key]));
        $checkLabels = [
            'backup_package' => 'پکیج بک‌آپ',
            'access_key' => 'Access Key',
            'secret_key' => 'Secret Key',
            'bucket' => 'Bucket',
            'endpoint' => 'Endpoint',
            'region' => 'Region',
            'archive_password' => 'رمز ۳۲ کاراکتری آرشیو',
            'zip_extension' => 'افزونه ZIP سرور',
            'database_dump_binary' => 'ابزار mysqldump',
        ];
        $statusLabels = ['queued' => 'در صف', 'running' => 'در حال اجرا', 'succeeded' => 'موفق', 'failed' => 'ناموفق'];
        $statusClasses = [
            'queued' => 'bg-amber-50 text-amber-700 border-amber-200',
            'running' => 'bg-sky-50 text-sky-700 border-sky-200',
            'succeeded' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
        ];
    @endphp

    <x-admin.page>
        <div class="space-y-6">
            <x-admin.metabox title="آمادگی سرور و Object Storage" :collapsible="false">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($environmentChecks as $key => $ready)
                        <div
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                            <span class="font-bold text-slate-700">{{ $checkLabels[$key] }}</span>
                            <span
                                class="{{ $ready ? 'text-emerald-600' : 'text-rose-600' }}">{{ $ready ? 'آماده' : 'ناقص' }}</span>
                        </div>
                    @endforeach
                </div>
            </x-admin.metabox>

            <x-admin.form action="{{ route('admin.settings.backup.store') }}" method="put">
                <div class="space-y-6">
                    <x-admin.metabox title="زمان‌بندی و محتوای بک‌آپ" :collapsible="false">
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                            @foreach([
                                'enabled' => ['بک‌آپ خودکار', 'فعال‌سازی اجرای زمان‌بندی‌شده'],
                                'files_enabled' => ['فایل‌ها و کد', 'کد، تنظیمات و فایل‌های آپلودی'],
                                'database_enabled' => ['دیتابیس اصلی', 'خروجی سازگار MySQL با تراکنش واحد'],
                            ] as $key => [$label, $hint])
                                <div class="flex items-center gap-3">
                                    <x-admin.form.switch :name="$key" :checked="$get($key)"/>
                                    <div>
                                        <div class="text-xs font-bold text-slate-700">{{ $label }}</div>
                                        <div class="mt-1 text-[11px] text-slate-400">{{ $hint }}</div>
                                    </div>
                                </div>
                            @endforeach

                            <x-admin.field label="فاصله بک‌آپ کامل (ساعت)"
                                           :error="$errors->first('full_interval_hours')">
                                <x-admin.form.input type="number" min="6" max="168" name="full_interval_hours"
                                                    :value="$get('full_interval_hours')"/>
                            </x-admin.field>
                            <x-admin.field label="فاصله بک‌آپ دیتابیس (ساعت)"
                                           :error="$errors->first('database_interval_hours')">
                                <x-admin.form.input type="number" min="1" max="24" name="database_interval_hours"
                                                    :value="$get('database_interval_hours')"/>
                            </x-admin.field>
                        </div>
                    </x-admin.metabox>

                    <x-admin.metabox title="سیاست نگه‌داری و سقف فضا" :collapsible="false">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <x-admin.field label="نگه‌داری همه نسخه‌ها (روز)" :error="$errors->first('keep_all_days')">
                                <x-admin.form.input type="number" min="1" max="30" name="keep_all_days"
                                                    :value="$get('keep_all_days')"/>
                            </x-admin.field>
                            <x-admin.field label="نسخه‌های روزانه (روز)" :error="$errors->first('keep_daily_days')">
                                <x-admin.form.input type="number" min="7" max="180" name="keep_daily_days"
                                                    :value="$get('keep_daily_days')"/>
                            </x-admin.field>
                            <x-admin.field label="نسخه‌های هفتگی (هفته)" :error="$errors->first('keep_weekly_weeks')">
                                <x-admin.form.input type="number" min="4" max="52" name="keep_weekly_weeks"
                                                    :value="$get('keep_weekly_weeks')"/>
                            </x-admin.field>
                            <x-admin.field label="نسخه‌های ماهانه (ماه)" :error="$errors->first('keep_monthly_months')">
                                <x-admin.form.input type="number" min="3" max="36" name="keep_monthly_months"
                                                    :value="$get('keep_monthly_months')"/>
                            </x-admin.field>
                            <x-admin.field label="نسخه‌های سالانه (سال)" :error="$errors->first('keep_yearly_years')">
                                <x-admin.form.input type="number" min="1" max="5" name="keep_yearly_years"
                                                    :value="$get('keep_yearly_years')"/>
                            </x-admin.field>
                            <x-admin.field label="حداکثر فضای مصرفی (GB)" :error="$errors->first('storage_limit_gb')">
                                <x-admin.form.input type="number" min="10" max="145" name="storage_limit_gb"
                                                    :value="$get('storage_limit_gb')"/>
                            </x-admin.field>
                        </div>
                        <p class="mt-4 text-[11px] leading-6 text-slate-500">با فضای ۱۵۰ گیگ، سقف پیشنهادی ۱۴۰ گیگ است
                            تا حاشیه امن برای آپلود نسخه جدید باقی بماند.</p>
                    </x-admin.metabox>

                    <x-admin.form.save-button/>
                </div>
            </x-admin.form>

            <x-admin.metabox title="اجرای دستی و سوابق" :collapsible="false">
                <div class="mb-5 flex flex-wrap gap-3">
                    <form method="post" action="{{ route('admin.settings.backup.run') }}">
                        @csrf
                        <input type="hidden" name="type" value="full">
                        <x-admin.form.button type="submit">اجرای بک‌آپ کامل</x-admin.form.button>
                    </form>
                    <form method="post" action="{{ route('admin.settings.backup.run') }}">
                        @csrf
                        <input type="hidden" name="type" value="database">
                        <x-admin.form.button type="submit" variant="secondary">فقط دیتابیس</x-admin.form.button>
                    </form>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-right text-xs">
                        <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3">زمان</th>
                            <th class="px-4 py-3">نوع</th>
                            <th class="px-4 py-3">مبدأ</th>
                            <th class="px-4 py-3">وضعیت</th>
                            <th class="px-4 py-3">پیام</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($backupRuns as $run)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $run->created_at?->format('Y/m/d H:i') }}</td>
                                <td class="px-4 py-3 font-bold text-slate-700">{{ $run->type === 'full' ? 'کامل' : 'دیتابیس' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $run->trigger === 'manual' ? 'دستی' : 'خودکار' }}</td>
                                <td class="px-4 py-3"><span
                                        class="rounded-full border px-2 py-1 {{ $statusClasses[$run->status] ?? '' }}">{{ $statusLabels[$run->status] ?? $run->status }}</span>
                                </td>
                                <td class="max-w-md truncate px-4 py-3 text-slate-500"
                                    title="{{ $run->message }}">{{ $run->message ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">هنوز اجرایی ثبت نشده است.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.metabox>
        </div>
    </x-admin.page>
@endsection
