@if(get_setting('home', 'ads_4_display_background'))
    <x-front.cart class="tinymce-content">
        {!! get_setting('home', 'ads_4_body') !!}
    </x-front.cart>
@else
    <div class="tinymce-content">
        {!! get_setting('home', 'ads_4_body') !!}
    </div>
@endif
