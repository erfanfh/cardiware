<x-admin.metabox title="الگوهای پیامک اعلان ورود">
    <p class="mb-5 text-sm leading-7 text-slate-600">
        اعلان ورود از سرویس پیامکی پیش‌فرض استفاده می‌کند و الگوی آن از OTP جداست.
        نمونهٔ متن: «{Name} عزیز، ورود به حساب شما در {Time} از IP {Ip} در {Source} انجام شد. اگر شما نبودید با پشتیبانی تماس بگیرید.»
        فعال‌سازی ارسال برای هر نقش از فهرست نقش‌ها، گزینهٔ «اعلان ورود» انجام می‌شود.
    </p>
    @foreach(['smsir' => 'SMS.ir', 'farazsms' => 'فراز اس‌ام‌اس', 'melipayamak' => 'ملی پیامک'] as $provider => $label)
        <div class="mb-5 rounded-xl border border-slate-200 p-4">
            <h3 class="mb-4 text-sm font-bold">{{ $label }}</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <x-admin.field label="شناسه الگوی اعلان ورود">
                    <x-admin.form.input :name="$provider . '_login_pattern'" :value="old($provider . '_login_pattern', get_setting('general', $provider . '_login_pattern'))"/>
                </x-admin.field>
                @if($provider !== 'melipayamak')
                    @foreach(['name' => 'نام کاربر', 'time' => 'زمان ورود', 'ip' => 'IP', 'source' => 'محل ورود'] as $key => $title)
                        @php($field = $provider . '_login_' . $key . '_parameter')
                        <x-admin.field :label="'نام متغیر ' . $title">
                            <x-admin.form.input :name="$field" :value="old($field, get_setting('general', $field) ?: ucfirst($key))"/>
                        </x-admin.field>
                    @endforeach
                @endif
            </div>
            @if($provider === 'melipayamak')
                <p class="mt-3 text-xs text-slate-500">ترتیب متغیرهای الگو: نام کاربر، زمان ورود (تهران)، IP، محل ورود.</p>
            @endif
        </div>
    @endforeach
</x-admin.metabox>
