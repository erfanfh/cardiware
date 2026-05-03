@php
    /** @var \App\Application\Seo\DTOs\SeoDataDTO $seoData */

    $tags = ['<!-- Moddingway.ir SEO Module -->'];

    if (filled($seoData->title)) {
        $tags[] = '<title>' . e($seoData->title) . '</title>';
    }

    if (filled($seoData->robots)) {
        $tags[] = '<meta name="robots" content="' . e($seoData->robots) . '">';
    }

    if (filled($seoData->description)) {
        $tags[] = '<meta name="description" content="' . e($seoData->description) . '">';
    }

    if (filled($seoData->canonicalUrl)) {
        $tags[] = '<link rel="canonical" href="' . e($seoData->canonicalUrl) . '">';
    }

    if ($seoData->pagination?->prev) {
        $tags[] = '<link rel="prev" href="' . e($seoData->pagination->prev) . '">';
    }

    if ($seoData->pagination?->next) {
        $tags[] = '<link rel="next" href="' . e($seoData->pagination->next) . '">';
    }

    if (filled($seoData->author)) {
        $tags[] = '<meta name="author" content="' . e($seoData->author) . '">';
    }

    foreach (($seoData->openGraph?->toMetaArray() ?? []) as $property => $content) {
        if (filled($content)) {
            $tags[] = '<meta property="' . e($property) . '" content="' . e($content) . '">';
        }
    }

    foreach (($seoData->twitter?->toMetaArray() ?? []) as $name => $content) {
        if (filled($content)) {
            $tags[] = '<meta name="' . e($name) . '" content="' . e($content) . '">';
        }
    }

    if($seoData->schemaJson){
        $tags[] = '<script type="application/ld+json">' . $seoData->schemaJson . '</script>';
    }

    foreach (($seoData->verification?->toMetaArray() ?? []) as $name => $content) {
        if (filled($content)) {
            $tags[] = '<meta name="' . e($name) . '" content="' . e($content) . '">';
        }
    }

    $tags[] = '<!-- Moddingway.ir SEO Module -->';
@endphp

{!! implode("\n\t", $tags) !!}
