@php
    $downloadBanner = $post->downloadBanner;
@endphp

@if($downloadBanner?->url)
    <div data-post-download-banner class="mt-6 overflow-hidden rounded-2xl">
        @if($post->download_banner_url)
            <a href="{{ $post->download_banner_url }}" class="block rounded-2xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-500">
        @endif

        <x-front.responsive-image
            :media="$downloadBanner"
            :fallback="$downloadBanner->url"
            :alt="$downloadBanner->title ?: $post->title"
            variant="large"
            class="block h-auto w-full"
            wrapper-class="w-full"
            sizes="(max-width: 1024px) 100vw, 70vw"
            :show-placeholder="false"
        />

        @if($post->download_banner_url)
            </a>
        @endif
    </div>
@endif
