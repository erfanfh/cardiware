@if($product->updateProgress?->is_visible)
    @php
        $update = $product->updateProgress;

        $progress = max(
            0,
            min(100, (int) $update->progress_percentage)
        );

        $isPublished = $progress >= 100;

        // Continuous color: 0% red → 50% orange/yellow → 100% green (consistent with project cards)
        $hue = $isPublished ? 152 : (int) round($progress * 1.42);
        $hueEnd = min(160, $hue + 16);

        $accent = "hsl({$hue} 80% 45%)";
        $accentSoft = "hsl({$hue} 84% 52% / .14)";
        $accentBorder = "hsl({$hue} 84% 52% / .38)";
        $glow = "hsl({$hue} 84% 52% / .45)";
        $barGradient = "linear-gradient(to left, hsl({$hueEnd} 83% 47%), hsl({$hue} 88% 53%))";

        $startedAt = $update->started_at
            ? (($update->started_at) ? \Illuminate\Support\Carbon::parse($update->started_at)->format('Y/m/d') : '')
            : null;

        $releaseAt = $update->release_at
            ? (($update->release_at) ? \Illuminate\Support\Carbon::parse($update->release_at)->format('Y/m/d') : '')
            : null;
    @endphp

    <section
            class="mw-upbox {{ $isPublished ? 'is-done' : '' }}"
            style="--mw-up-accent: {{ $accent }}; --mw-up-accent-soft: {{ $accentSoft }}; --mw-up-accent-border: {{ $accentBorder }}; --mw-up-glow: {{ $glow }}; --mw-up-grad: {{ $barGradient }};"
    >
        <span class="mw-upbox-halo" aria-hidden="true"></span>

        <div class="mw-upbox-head">
            <span class="mw-upbox-icon {{ $isPublished ? '' : 'is-spinning' }}" aria-hidden="true">
                @include('icons.arrow-reload-horizontal')
            </span>

            <div class="min-w-0 flex-1">
                <div class="mw-upbox-title">
                    {{ $update->display_title }}
                </div>

                @if($update->display_description)
                    <div class="mw-upbox-desc">
                        {{ $update->display_description }}
                    </div>
                @endif
            </div>

            <span class="mw-upbox-badge {{ $isPublished ? 'is-done' : '' }}">
                <span class="mw-upbox-badge-dot"></span>
                {{ $isPublished ? 'Published' : 'Preparing' }}
            </span>
        </div>

        {{-- Large percentage with animated counter --}}
        <div class="mw-upbox-stage">
            <div class="mw-upbox-count">
                <b class="mw-count" data-count-to="{{ $progress }}">0</b>
                <span class="mw-upbox-count-unit">%</span>
            </div>

            <div class="mw-upbox-count-label">
                Completed
                <span>out of 100%</span>
            </div>
        </div>

        {{-- Progress bar: auto-fill on visibility + Animated texture + Moving tip --}}
        <div
                class="mw-upbox-bar"
                role="progressbar"
                aria-label="{{ $update->display_title }}"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="{{ $progress }}"
        >
            <div
                    class="mw-upbox-fill mw-fill-anim {{ $isPublished ? 'is-done' : '' }}"
                    data-fill-target="{{ $progress }}%"
                    style="width: 0;"
            ></div>

            @if($progress > 3 && $progress < 97)
                <span class="mw-upbox-tip mw-fill-tip" data-fill-target="{{ $progress }}%"
                      style="left: calc(0% - 9px);"
                      aria-hidden="true">
                    <x-front.arrow-right-02/>
                </span>
            @endif

            @if($isPublished)
                <span class="mw-upbox-flag" aria-hidden="true">
                    @include('icons.target-01')
                </span>
            @endif
        </div>

        <div class="mw-upbox-dates">
            @if($startedAt)
                <div class="mw-upbox-date">
                    <span class="mw-upbox-date-icon" aria-hidden="true">
                        @include('icons.calendar-03')
                    </span>

                    <span class="mw-upbox-date-label">Development started</span>

                    <b>{{ $startedAt }}</b>
                </div>
            @endif

            @if($releaseAt)
                <div class="mw-upbox-date">
                    <span class="mw-upbox-date-icon" aria-hidden="true">
                        @include('icons.calendar-03')
                    </span>

                    <span class="mw-upbox-date-label">Published date</span>

                    <b>{{ $releaseAt }}</b>
                </div>
            @endif
        </div>

        <div class="mw-upbox-foot {{ $isPublished ? 'is-done' : '' }}">
            @if($isPublished)
                <span class="mw-upbox-foot-check" aria-hidden="true">
                    @include('icons.checkmark-badge-01')
                </span>

                Update released
            @else
                <span class="mw-upbox-dots" aria-hidden="true">
                    <i></i><i></i><i></i>
                </span>

                Update on the way
            @endif
        </div>
    </section>
@endif
