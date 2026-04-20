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

    foreach (($seoData->verification?->toMetaArray() ?? []) as $name => $content) {
        if (filled($content)) {
            $tags[] = '<meta name="' . e($name) . '" content="' . e($content) . '">';
        }
    }

    $schemaContext = app(\App\Application\Seo\Actions\Schema\BuildHomePageSchemaContextAction::class)->handle();
    $schemaGraph = app(\App\Application\Seo\Actions\Schema\BuildSchemaGraphAction::class)->handle($schemaContext);
    $schemaJson = app(\App\Application\Seo\Actions\Schema\RenderSchemaGraphAction::class)->handle($schemaGraph);

    if($schemaJson){
        $tags[] = '<script type="application/ld+json">' . $schemaJson . '</script>';
    }

    $tags[] = '<!-- Moddingway.ir SEO Module -->';
@endphp

{!! implode("\n\t", $tags) !!}
