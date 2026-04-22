@php
    $seoBoxConfig = app(\App\Application\Seo\Actions\Boxes\BuildProjectSeoBoxConfigAction::class)->execute($project ?? null);
@endphp

<x-admin.seo.box :config="$seoBoxConfig"/>
