<x-admin.field
    label="شناسه الگوی اعلان ورود"
    :hint="$provider === 'melipayamak' ? 'ترتیب متغیرهای الگو: نام کاربر، زمان ورود (تهران)، IP، محل ورود.' : null"
>
    <x-admin.form.input
        :name="$provider . '_login_pattern'"
        :value="old($provider . '_login_pattern', get_setting('general', $provider . '_login_pattern'))"
    />
</x-admin.field>

@if($provider !== 'melipayamak')
    @foreach(['name' => 'نام کاربر', 'time' => 'زمان ورود', 'ip' => 'IP', 'source' => 'محل ورود'] as $key => $title)
        @php($field = $provider . '_login_' . $key . '_parameter')
        <x-admin.field :label="'نام متغیر ' . $title . ' در الگوی اعلان ورود'">
            <x-admin.form.input
                :name="$field"
                :value="old($field, get_setting('general', $field) ?: ucfirst($key))"
            />
        </x-admin.field>
    @endforeach
@endif
