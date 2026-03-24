@props([
    'name' => null,
    'id' => null,
    'value' => null,

    'label' => null,
    'hint' => null,
    'error' => null,
    'required' => false,

    'multiple' => false,
    'disabled' => false,

    'placeholder' => 'جستجو و انتخاب...',
    'searchUrl' => null,

    // selected options for initial render
    // [
    //   ['value' => 1, 'label' => 'دسته اول'],
    //   ['value' => 2, 'label' => 'دسته دوم'],
    // ]
    'selectedOptions' => [],

    'minChars' => 2,
    'maxOptions' => 20,
    'maxItems' => null,
    'allowClear' => true,
])

@php
    $inputId = $id ?: 'async-select-' . uniqid();

    $isMultiple = filter_var($multiple, FILTER_VALIDATE_BOOL);
    $isDisabled = filter_var($disabled, FILTER_VALIDATE_BOOL);
    $isRequired = filter_var($required, FILTER_VALIDATE_BOOL);
    $isAllowClear = filter_var($allowClear, FILTER_VALIDATE_BOOL);

    $normalizedValue = match (true) {
        is_array($value) => $value,
        filled($value) => [$value],
        default => [],
    };

    $selectedOptions = collect($selectedOptions)
        ->map(function ($item) {
            return [
                'value' => (string) data_get($item, 'value'),
                'label' => (string) data_get($item, 'label'),
            ];
        })
        ->filter(fn ($item) => $item['value'] !== '' && $item['label'] !== '')
        ->values();

    $selectedValuesFromOptions = $selectedOptions->pluck('value')->all();

    foreach ($normalizedValue as $item) {
        $item = (string) $item;

        if (!in_array($item, $selectedValuesFromOptions, true)) {
            $selectedOptions->push([
                'value' => $item,
                'label' => '#' . $item,
            ]);
        }
    }

    $finalMaxItems = $isMultiple ? ($maxItems ?: null) : 1;

    $nameProvidedViaAttributes = $attributes->has(':name') || $attributes->has('x-bind:name');
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-bold text-slate-700">
            {{ $label }}
            @if($isRequired)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div
        x-data="mwAsyncSelect({
            multiple: @js($isMultiple),
            placeholder: @js($placeholder),
            searchUrl: @js($searchUrl),
            minChars: @js((int) $minChars),
            maxOptions: @js((int) $maxOptions),
            maxItems: @js($finalMaxItems),
            allowClear: @js($isAllowClear),
            initialOptions: @js($selectedOptions->values()->all()),
        })"
        x-init="init($refs.select)"
        class="mw-async-select {{ $error ? 'mw-async-select--invalid' : '' }}"
    >
        <select
            x-ref="select"
            id="{{ $inputId }}"
            @if(!$nameProvidedViaAttributes && $name)
                name="{{ $isMultiple ? (str_ends_with($name, '[]') ? $name : $name . '[]') : $name }}"
            @endif
            @if($isMultiple) multiple @endif
            @if($isDisabled) disabled @endif
            @if($isRequired) required @endif
            {{ $attributes->except(['name', 'id', 'value'])->merge([
                'class' => 'mw-async-select__native w-full',
            ]) }}
        >
            @foreach($selectedOptions as $option)
                <option value="{{ $option['value'] }}" selected>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    @if($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="text-xs font-medium text-rose-600">{{ $error }}</p>
    @endif
</div>
