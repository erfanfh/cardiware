@props([
    'mode' => 'field', // field | repeater
    'name',
    'idName' => null,
    'value' => null,
    'idValue' => null,
    'placeholder' => 'یک آیکون انتخاب کنید',
    'hint' => 'برای مشاهده کتابخانه آیکون‌ها کلیک کنید.',
    'compact' => false,
])

@php
    $isRepeater = $mode === 'repeater';

    $resolvedIdName = $idName;

    if (blank($resolvedIdName)) {
        $resolvedIdName = str_ends_with($name, ']')
            ? preg_replace('/\[([^\]]+)]$/', '[${1}_id]', $name)
            : $name . '_id';
    }

    $selectedTitle = !$isRepeater
        ? (is_array($value)
            ? data_get($value, 'title', data_get($value, 'full_name', data_get($value, 'name')))
            : $value)
        : null;

    $selectedId = !$isRepeater
        ? ($idValue ?? (is_array($value) ? data_get($value, 'id') : null))
        : null;

    $initialIcon = !$isRepeater && filled($selectedTitle)
        ? app(\App\Application\Icon\Actions\ResolveIconDetailsAction::class)->execute($selectedTitle)
        : null;

    $selectedId ??= data_get($initialIcon, 'id');
    $initialSvg = data_get($initialIcon, 'svg');

    $lookupUrl = route('admin.icons.lookup');
    $itemKey = 'icon-field-' . md5(($name ?? 'icon') . '-' . uniqid('', true));
@endphp

<div
    @if($isRepeater)
        x-data="mwRepeaterIconPicker({
            item: it,
            fieldName: @js($name),
            idFieldName: @js($resolvedIdName),
            lookupUrl: @js($lookupUrl),
        })"
    @else
        x-data="mwIconPickerField({
            itemKey: @js($itemKey),
            value: {
                id: @js($selectedId),
                title: @js($selectedTitle),
                svg: @js($initialSvg),
            },
            lookupUrl: @js($lookupUrl),
        })"
    @endif
    x-on:mwip-selected.window="applySelection($event.detail)"
    x-on:mwip-cleared.window="applyCleared($event.detail)"
    class="mw-icon-picker {{ $compact ? 'mw-icon-picker--compact' : '' }}"
>
    <div class="mw-icon-picker__card">
        <button
            type="button"
            class="mw-icon-picker__main"
            @click="openPicker()"
        >
            <span class="mw-icon-picker__preview" :class="{ 'is-empty': !getSelectedTitle() }">
                <span
                    x-show="loadingPreview"
                    x-cloak
                    class="mw-icon-picker__loader"
                    aria-hidden="true"
                ></span>

                <span
                    x-show="!loadingPreview && selectedSvg"
                    x-cloak
                    class="mw-icon-picker__svg"
                    x-html="selectedSvg"
                ></span>

                <span
                    x-show="!loadingPreview && !selectedSvg"
                    class="mw-icon-picker__placeholder-icon"
                    aria-hidden="true"
                >
                    @include('icons.image-03')
                </span>
            </span>

            <span class="mw-icon-picker__content">
                <span
                    class="mw-icon-picker__title"
                    x-text="getSelectedTitle() || @js($placeholder)"
                    :class="{ 'is-placeholder': !getSelectedTitle() }"
                ></span>

                <span class="mw-icon-picker__hint">
                    {{ $hint }}
                </span>
            </span>

            <span
                class="mw-icon-picker__action"
                x-text="getSelectedTitle() ? 'تغییر آیکون' : 'انتخاب آیکون'"
            ></span>
        </button>

        <button
            type="button"
            class="mw-icon-picker__clear"
            x-show="getSelectedTitle()"
            x-cloak
            @click="clearSelection()"
            title="حذف آیکون انتخاب شده"
        >
            @include('icons.cancel-01')
        </button>
    </div>

    @if($isRepeater)
        <input
            type="hidden"
            x-bind:name="field(i, idFieldName)"
            x-model="item[idFieldName]"
        >

        <input
            type="hidden"
            x-bind:name="field(i, fieldName)"
            x-model="item[fieldName]"
        >
    @else
        <input type="hidden" name="{{ $resolvedIdName }}" x-model="selectedId">
        <input type="hidden" name="{{ $name }}" x-model="selectedTitle">
    @endif
</div>
