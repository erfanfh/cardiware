@if(get_setting('home', 'ads_2_display_background'))
    <x-front.cart class="tinymce-content">
        {!! get_setting('home', 'ads_2_body') !!}
    </x-front.cart>
@else
    <div class="tinymce-content">
        {!! get_setting('home', 'ads_2_body') !!}
    </div>
@endif
