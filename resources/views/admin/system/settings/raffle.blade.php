@extends('admin.layout')

@section('title', 'تنظیمات قرعه‌کشی')

@section('content')
    @php
        $get = fn (string $key, mixed $default = null) => old($key, get_setting('raffle', $key, $default));
    @endphp

    <x-admin.form action="{{ route('admin.settings.raffle.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-5">
                <div class="rounded-2xl border border-brand-200 bg-brand-50 p-4 text-xs leading-7 text-brand-800">
                    قالب پیامک برنده با قالب OTP متفاوت است. ابتدا در پنل سرویس پیامکی یک قالب مستقل بسازید و شناسه و نام متغیرهای همان قالب را اینجا وارد کنید.
                </div>

                <x-admin.metabox title="رفتار عمومی قرعه‌کشی" :collapsible="false">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                        <div class="flex items-center gap-3">
                            <x-admin.form.switch name="winner_sms_enabled" :checked="$get('winner_sms_enabled', false)"/>
                            <div>
                                <div class="text-xs font-bold text-slate-700">پیامک برنده</div>
                                <div class="mt-1 text-[11px] text-slate-400">ارسال پس از انتخاب برنده اصلی یا ارتقای ذخیره</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-admin.form.switch name="winner_reminder_sms_enabled" :checked="$get('winner_reminder_sms_enabled', false)"/>
                            <div>
                                <div class="text-xs font-bold text-slate-700">یادآوری مهلت تأیید</div>
                                <div class="mt-1 text-[11px] text-slate-400">فقط برای برنده در انتظار تأیید</div>
                            </div>
                        </div>
                        <x-admin.field label="ارسال یادآوری، چند ساعت مانده به مهلت" :error="$errors->first('winner_reminder_hours_before_deadline')">
                            <x-admin.form.input type="number" min="1" max="168" name="winner_reminder_hours_before_deadline" :value="$get('winner_reminder_hours_before_deadline', 12)"/>
                        </x-admin.field>
                        <div class="flex items-center gap-3">
                            <x-admin.form.switch name="auto_start_draw_enabled" :checked="$get('auto_start_draw_enabled', false)"/>
                            <div>
                                <div class="text-xs font-bold text-slate-700">شروع خودکار قرعه‌کشی</div>
                                <div class="mt-1 text-[11px] text-slate-400">کمپین بسته در زمان draw_at وارد صف اجرا شود</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-admin.form.switch name="show_in_header_navigation" :checked="$get('show_in_header_navigation', true)"/>
                            <div>
                                <div class="text-xs font-bold text-slate-700">نمایش در منوی سایت</div>
                                <div class="mt-1 text-[11px] text-slate-400">لینک قرعه‌کشی‌ها به منوی اصلی افزوده شود</div>
                            </div>
                        </div>
                    </div>
                </x-admin.metabox>

                @foreach([
                    'smsir' => ['title' => 'SMS.ir', 'winner_id' => 'smsir_winner_template_id', 'reminder_id' => 'smsir_reminder_template_id', 'suffix' => 'parameter'],
                    'farazsms' => ['title' => 'فراز اس‌ام‌اس', 'winner_id' => 'farazsms_winner_pattern', 'reminder_id' => 'farazsms_reminder_pattern', 'suffix' => 'attribute'],
                ] as $provider => $config)
                    <x-admin.metabox :title="'قالب‌های ' . $config['title']" :collapsible="true">
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <x-admin.field label="شناسه قالب اعلام برنده" :error="$errors->first($config['winner_id'])">
                                <x-admin.form.input :name="$config['winner_id']" :value="$get($config['winner_id'])" dir="ltr"/>
                            </x-admin.field>
                            <x-admin.field label="شناسه قالب یادآوری" :error="$errors->first($config['reminder_id'])">
                                <x-admin.form.input :name="$config['reminder_id']" :value="$get($config['reminder_id'])" dir="ltr"/>
                            </x-admin.field>

                            @foreach(['winner' => 'اعلام برنده', 'reminder' => 'یادآوری'] as $type => $typeLabel)
                                @foreach(['name' => 'نام کاربر', 'campaign' => 'عنوان قرعه‌کشی', 'prize' => 'عنوان جایزه', 'deadline' => 'مهلت تأیید'] as $key => $label)
                                    @php($field = "{$provider}_{$type}_{$key}_{$config['suffix']}")
                                    <x-admin.field :label="'نام متغیر ' . $label . ' — ' . $typeLabel" :error="$errors->first($field)">
                                        <x-admin.form.input :name="$field" :value="$get($field, ucfirst($key))" dir="ltr"/>
                                    </x-admin.field>
                                @endforeach
                            @endforeach
                        </div>
                    </x-admin.metabox>
                @endforeach

                <x-admin.metabox title="قالب‌های ملی‌پیامک" :collapsible="true">
                    <div class="rounded-xl bg-slate-50 p-3 text-[11px] leading-6 text-slate-500">
                        ترتیب متغیرهای هر دو قالب باید دقیقاً این باشد: نام کاربر؛ عنوان قرعه‌کشی؛ عنوان جایزه؛ مهلت تأیید.
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <x-admin.field label="شناسه قالب اعلام برنده" :error="$errors->first('melipayamak_winner_pattern')">
                            <x-admin.form.input name="melipayamak_winner_pattern" :value="$get('melipayamak_winner_pattern')" dir="ltr"/>
                        </x-admin.field>
                        <x-admin.field label="شناسه قالب یادآوری" :error="$errors->first('melipayamak_reminder_pattern')">
                            <x-admin.form.input name="melipayamak_reminder_pattern" :value="$get('melipayamak_reminder_pattern')" dir="ltr"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>
        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
