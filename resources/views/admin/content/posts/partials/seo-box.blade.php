@php
    $seoBoxConfig = app(\App\Application\Seo\Actions\Boxes\BuildPostSeoBoxConfigAction::class)->execute($post ?? null);
@endphp

<x-admin.seo.box :config="$seoBoxConfig"/>
