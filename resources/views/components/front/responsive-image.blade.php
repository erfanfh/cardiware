@php
    $imageSrc = $src();
    $imageSrcset = $srcset();
    $imageSizes = $resolvedSizes();
    $altText = $alt ?? '';
    $imageWidth = $resolvedWidth();
    $imageHeight = $resolvedHeight();
    $imageFetchpriority = $resolvedFetchpriority();
@endphp

@if($imageSrc)
    <div
        x-data="{ loaded: false }"
        x-init="
            $nextTick(() => {
                if ($refs.image && $refs.image.complete) {
                    loaded = true;
                }
            })
        "
        class="overflow-hidden {{ $wrapperClass }} {{ $wrapperClass && str_contains($wrapperClass, 'absolute') ? '' : 'relative' }}"
        x-bind:class="loaded ? 'bg-transparent' : 'bg-slate-100 dark:bg-slate-800'"
    >
        @if($skeleton)
            <div
                x-show="! loaded"
                x-transition.opacity.duration.300ms
                class="pointer-events-none absolute inset-0 z-10 overflow-hidden rounded-[inherit]
                       bg-slate-100 dark:bg-slate-800"
            >
                {{-- Soft light texture --}}
                <div
                    class="absolute inset-0 opacity-80
                           bg-[radial-gradient(circle_at_28%_20%,rgba(255,255,255,0.95),transparent_34%),radial-gradient(circle_at_78%_74%,rgba(226,232,240,0.95),transparent_38%)]
                           dark:bg-[radial-gradient(circle_at_28%_20%,rgba(51,65,85,0.75),transparent_34%),radial-gradient(circle_at_78%_74%,rgba(15,23,42,0.95),transparent_38%)]"
                ></div>

                {{-- Soft bottom depth --}}
                <div
                    class="absolute inset-x-0 bottom-0 h-1/2
                           bg-gradient-to-t from-slate-200/70 via-slate-100/20 to-transparent
                           dark:from-slate-950/45 dark:via-slate-900/10"
                ></div>

                {{-- Shimmer --}}
                <div
                    class="absolute inset-0 -translate-x-full
                           animate-[image-skeleton-shimmer_1.45s_ease-in-out_infinite]
                           bg-gradient-to-r from-transparent via-white/75 to-transparent
                           dark:via-white/10"
                ></div>
            </div>
        @endif

        <img
            x-ref="image"
            src="{{ $imageSrc }}"
            @if($imageSrcset) srcset="{{ $imageSrcset }}" @endif
            @if($imageSizes) sizes="{{ $imageSizes }}" @endif
            @if($title) title="{{ $title }}" @endif
            alt="{{ $altText }}"
            class="{{ $class }} transition-opacity duration-500"
            x-bind:class="loaded ? 'opacity-100' : 'opacity-0'"
            x-on:load="loaded = true"
            x-on:error="loaded = true"
            loading="{{ $loading() }}"
            decoding="{{ $decoding() }}"
            @if($imageWidth) width="{{ $imageWidth }}" @endif
            @if($imageHeight) height="{{ $imageHeight }}" @endif
            @if($imageFetchpriority) fetchpriority="{{ $imageFetchpriority }}" @endif
        >
    </div>
@elseif($showPlaceholder)
    <div
        class="relative overflow-hidden rounded-[inherit] aspect-video
               bg-slate-100 border border-slate-200
               dark:border-slate-700 dark:bg-slate-800 {{ $class }}"
    >
        @if($skeleton)
            <div
                class="absolute inset-0 opacity-80
                       bg-[radial-gradient(circle_at_28%_20%,rgba(255,255,255,0.95),transparent_34%),radial-gradient(circle_at_78%_74%,rgba(226,232,240,0.95),transparent_38%)]
                       dark:bg-[radial-gradient(circle_at_28%_20%,rgba(51,65,85,0.75),transparent_34%),radial-gradient(circle_at_78%_74%,rgba(15,23,42,0.95),transparent_38%)]"
            ></div>

            <div
                class="absolute inset-x-0 bottom-0 h-1/2
                       bg-gradient-to-t from-slate-200/70 via-slate-100/20 to-transparent
                       dark:from-slate-950/45 dark:via-slate-900/10"
            ></div>

            <div
                class="absolute inset-0 -translate-x-full
                       animate-[image-skeleton-shimmer_1.45s_ease-in-out_infinite]
                       bg-gradient-to-r from-transparent via-white/75 to-transparent
                       dark:via-white/10"
            ></div>
        @endif
    </div>
@endif
