@if(get_setting('header', 'announcement_active'))
    @php
        $backgroundImage = get_setting('header', 'announcement_background_image')?->path;
        // Preserve existing backgrounds until a type is explicitly selected.
        $backgroundType = get_setting('header', 'announcement_background_type') ?: ($backgroundImage ? 'image' : 'color');
        $pattern = get_setting('header', 'announcement_pattern') ?: 'dots';
        $patternImage = get_setting('header', 'announcement_pattern_image')?->path;
        $patternSize = max(8, min(256, (int) (get_setting('header', 'announcement_pattern_size') ?: 32)));
        $duration = max(2, min(120, (int) (get_setting('header', 'announcement_animation_duration') ?: 12)));
        $angle = max(0, min(360, (int) (get_setting('header', 'announcement_gradient_angle') ?? 120)));
    @endphp
    <div class="header-announcement header-announcement--{{ $backgroundType }}"
         style="
            background-color: {{ get_setting('header', 'announcement_background_color') ?: '#ea580c' }};
            color: {{ get_setting('header', 'announcement_text_color') ?: '#ffffff' }};
            --announcement-base: {{ get_setting('header', 'announcement_background_color') ?: '#ea580c' }};
            --announcement-gradient: {{ get_setting('header', 'announcement_gradient_color') ?: '#7c3aed' }};
            --announcement-angle: {{ $angle }}deg;
            --announcement-duration: {{ $duration }}s;
            --announcement-pattern-color: {{ get_setting('header', 'announcement_pattern_color') ?: '#ffffff26' }};
            --announcement-pattern-size: {{ $patternSize }}px;
            @if($backgroundType === 'image' && $backgroundImage)
                background-image: url('{{ $backgroundImage }}');
            @endif
         ">
        @if($backgroundType === 'pattern')
            <div aria-hidden="true" class="header-announcement__pattern header-announcement__pattern--{{ $pattern }}"
                 @if($pattern === 'image' && $patternImage)
                     style="background-image: url('{{ $patternImage }}');"
                 @endif
            ></div>
        @endif
        <div class="header-announcement__content mx-auto flex max-w-7xl justify-between gap-1.5 px-4 py-2.5 flex-wrap items-center sm:gap-3 sm:py-2">
            <p class="text-sm/6 opacity-95 max-sm:text-xs max-sm:leading-6">
                {{ get_setting('header', 'announcement_text') }}
            </p>
            @if(get_setting('header', 'announcement_button_text'))
                <a href="{{ get_setting('header', 'announcement_button_url') }}"
                   class="header-announcement__button shrink-0 text-sm font-semibold max-sm:text-xs">
                    {{ get_setting('header', 'announcement_button_text') }}
                </a>
            @endif
        </div>
    </div>
@endif
