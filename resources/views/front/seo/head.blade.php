@php
    /** @var \App\Application\Seo\DTOs\SeoDataDTO $seoData */
@endphp

@if(filled($seoData->title))
    <title>{{ $seoData->title }}</title>
@endif
@if(filled($seoData->description))
    <meta name="description" content="{{ $seoData->description }}">
@endif
@if(filled($seoData->canonicalUrl))
    <link rel="canonical" href="{{ $seoData->canonicalUrl }}">
@endif
@if($seoData->openGraph)
    @foreach($seoData->openGraph->toMetaArray() as $property => $content)
        <meta property="{{ $property }}" content="{{ $content }}">
    @endforeach
@endif
