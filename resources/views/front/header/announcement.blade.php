@if(get_setting('header', 'announcement_active'))
    <div style="
        background-color: {{ get_setting('header', 'announcement_background_color') }};
        color: {{ get_setting('header', 'announcement_text_color') }};
        @if(get_setting('header', 'announcement_background_image'))
            background-image: url('{{ get_setting('header', 'announcement_background_image')->path }}');
            background-size: cover;
            background-position: center;
        @endif
    ">
        <div
            class="mx-auto flex max-w-7xl justify-between gap-1.5 px-4 py-2.5 flex-wrap items-center sm:gap-3 sm:py-2">
            <p class="text-sm/6 opacity-95 max-sm:text-xs max-sm:leading-6">
                {{ get_setting('header', 'announcement_text') }}
            </p>

            <a href="{{ get_setting('header', 'announcement_button_url') }}"
               class="shrink-0 text-sm font-semibold underline underline-offset-4 transition-all hover:opacity-90 max-sm:text-xs">
                {{ get_setting('header', 'announcement_button_text') }}
            </a>
        </div>
    </div>
@endif
