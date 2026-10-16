@foreach(\App\Domain\Notification\Enums\CustomerSmsType::cases() as $type)
    @php
        $prefix = $provider . '_' . $type->value;
        $labels = ['name' => 'نام کاربر', 'ticket_number' => 'شماره تیکت', 'order_number' => 'شماره سفارش'];
        $orderHint = $provider === 'melipayamak'
            ? 'ترتیب متغیرهای الگو: ' . implode('، ', array_map(fn ($key) => $labels[$key], array_keys($type->parameters())))
            : null;
    @endphp
    <x-admin.field :label="'شناسه الگوی ' . $type->label()" :hint="$orderHint">
        <x-admin.form.input
            :name="$prefix . '_pattern'"
            :value="old($prefix . '_pattern', get_setting('general', $prefix . '_pattern'))"
        />
    </x-admin.field>

    @if($provider !== 'melipayamak')
        @foreach($type->parameters() as $key => $defaultName)
            @php($field = $prefix . '_' . $key . '_parameter')
            <x-admin.field :label="'نام متغیر ' . $labels[$key] . ' در الگوی ' . $type->label()">
                <x-admin.form.input
                    :name="$field"
                    :value="old($field, get_setting('general', $field) ?: $defaultName)"
                />
            </x-admin.field>
        @endforeach
    @endif
@endforeach
