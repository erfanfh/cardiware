@php
    $body = (string) get_setting('sidebarWidgets', 'ads_'.$adNumber.'_body');
    $displayBackground = (bool) get_setting('sidebarWidgets', 'ads_'.$adNumber.'_display_background');
@endphp

@if(filled($body))
    @if($displayBackground)
        <x-front.cart class="tinymce-content">
            {!! $body !!}
        </x-front.cart>
    @else
        <div class="tinymce-content dark:text-slate-200">
            {!! $body !!}
        </div>
    @endif
@endif
