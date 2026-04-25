@if(get_setting('home', 'ads_3_display_background'))
    <x-front.cart class="tinymce-content">
        {!! get_setting('home', 'ads_3_body') !!}
    </x-front.cart>
@else
    <div class="tinymce-content dark:text-slate-200">
        {!! get_setting('home', 'ads_3_body') !!}
    </div>
@endif
