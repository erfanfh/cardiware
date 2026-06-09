@php
    $seoBoxConfig = app(\App\Application\Seo\Actions\Boxes\BuildProductSeoBoxConfigAction::class)->execute($product ?? null);
@endphp

<x-admin.seo.box :config="$seoBoxConfig"/>
