@if(get_setting('header', 'announcement_active'))
    <div style="
        background-color: {{ get_setting('header', 'announcement_background_color') }};
        color: {{ get_setting('header', 'announcement_text_color') }};
        @if(get_setting('header', 'announcement_background_image'))
            background-image: url('{{ get_setting('header', 'announcement_background_image')->path }}');
        @endif
    ">
        <div class="mx-auto max-w-7xl px-4 py-2 flex items-center justify-between gap-3">
            <p class="text-sm/6 opacity-95">{{ get_setting('header', 'announcement_text') }}</p>
            <a href="{{ get_setting('header', 'announcement_button_url') }}"
               class="text-sm font-semibold underline underline-offset-4 hover:opacity-90 transition-all">
                {{ get_setting('header', 'announcement_button_text') }}
            </a>
        </div>
    </div>
@endif
