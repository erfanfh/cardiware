@php
    $src = $component->src();
    $srcset = $component->srcset();
    $sizes = $component->resolvedSizes();
    $altText = $alt ?? '';
@endphp

@if($src)
    <img
        src="{{ $src }}"
        @if($srcset) srcset="{{ $srcset }}" @endif
        @if($sizes) sizes="{{ $sizes }}" @endif
        alt="{{ $altText }}"
        class="{{ $class }}"
        loading="{{ $component->loading() }}"
        decoding="{{ $component->decoding() }}"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        @if($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
    >
@elseif($showPlaceholder)
    <div class="bg-slate-100 border border-slate-200 dark:border-slate-700 dark:bg-slate-800 {{ $class }}"></div>
@endif
