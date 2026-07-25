@php
    $field = $field ?? '';
@endphp

@error($field)
<p class="mt-1 text-[11px] font-bold leading-5 text-red-600 dark:text-red-400">
    {{ $message }}
</p>
@enderror
