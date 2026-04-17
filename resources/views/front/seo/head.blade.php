@php
    /** @var \App\Application\Seo\DTOs\SeoDataDTO $seoData */
@endphp

@if(filled($seoData->title))
    <title>{{ $seoData->title }}</title>
@endif
@if(filled($seoData->description))
    <meta name="description" content="{{ $seoData->description }}">
@endif
@if(filled($seoData->robots))
    <meta name="robots" content="{{ $seoData->robots }}">
@endif
@if(filled($seoData->canonicalUrl))
    <link rel="canonical" href="{{ $seoData->canonicalUrl }}">
@endif
@if($seoData->openGraph)
    @foreach($seoData->openGraph->toMetaArray() as $property => $content)
        <meta property="{{ $property }}" content="{{ $content }}">
    @endforeach
@endif
@if($seoData->twitter)
    @foreach($seoData->twitter->toMetaArray() as $name => $content)
        <meta name="{{ $name }}" content="{{ $content }}">
    @endforeach
@endif
@if($seoData->verification)
    @foreach($seoData->verification->toMetaArray() as $name => $content)
        <meta name="{{ $name }}" content="{{ $content }}">
    @endforeach
@endif
@if($seoData->hasSchema())
    <script type="application/ld+json">{!! $seoData->schemaJson !!}</script>
@endif
