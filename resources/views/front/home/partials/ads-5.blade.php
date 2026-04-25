@if(get_setting('home', 'ads_5_display_background'))
    <x-front.cart class="tinymce-content">
        {!! get_setting('home', 'ads_5_body') !!}
    </x-front.cart>
@else
    <div class="tinymce-content dark:text-slate-200">
        {!! get_setting('home', 'ads_5_body') !!}
    </div>
@endif
