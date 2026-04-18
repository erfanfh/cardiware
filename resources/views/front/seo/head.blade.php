@php
    /** @var \App\Application\Seo\DTOs\SeoDataDTO $seoData */
@endphp

@if(filled($seoData->title))
    <title>{{ $seoData->title }}</title>
@endif
@if(filled($seoData->description))
    <meta name="description" content="{{ $seoData->description }}">
@endif
