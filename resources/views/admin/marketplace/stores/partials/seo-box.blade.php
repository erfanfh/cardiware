@php
    $seoBoxConfig = app(\App\Application\Seo\Actions\Boxes\BuildStoreSeoBoxConfigAction::class)->execute($store ?? null);
@endphp

<x-admin.seo.box :config="$seoBoxConfig"/>
