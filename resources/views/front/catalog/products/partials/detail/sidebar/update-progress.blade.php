@if($product->updateProgress?->is_visible)
    @php
        $update = $product->updateProgress;

        $progress = max(
            0,
            min(100, (int) $update->progress_percentage)
        );

        $isPublished = $progress >= 100;

        // رنگ پیوسته: ۰٪ قرمز → ۵۰٪ نارنجی/زرد → ۱۰۰٪ سبز (هماهنگ با کارت پروژه‌ها)
        $hue = $isPublished ? 152 : (int) round($progress * 1.42);
        $hueEnd = min(160, $hue + 16);

        $accent = "hsl({$hue} 80% 45%)";
        $accentSoft = "hsl({$hue} 84% 52% / .14)";
        $accentBorder = "hsl({$hue} 84% 52% / .38)";
        $glow = "hsl({$hue} 84% 52% / .45)";
        $barGradient = "linear-gradient(to left, hsl({$hueEnd} 83% 47%), hsl({$hue} 88% 53%))";

        $startedAt = $update->started_at
            ? jalali_date($update->started_at, 'Y/m/d')
            : null;

        $releaseAt = $update->release_at
            ? jalali_date($update->release_at, 'Y/m/d')
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
                {{ $isPublished ? 'منتشر شد' : 'در حال آماده‌سازی' }}
            </span>
        </div>

        {{-- درصد درشت با شمارنده متحرک --}}
        <div class="mw-upbox-stage">
            <div class="mw-upbox-count">
                <b class="mw-count" data-count-to="{{ $progress }}">0</b>
                <span class="mw-upbox-count-unit">٪</span>
            </div>

            <div class="mw-upbox-count-label">
                تکمیل شده
                <span>از ۱۰۰٪</span>
            </div>
        </div>

        {{-- نوار پیشرفت: پر شدن خودکار هنگام دیده‌شدن + بافت متحرک + نوک فلشر --}}
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
                      style="right: calc(0% - 9px);"
                      aria-hidden="true">
                    @include('icons.arrow-left-02')
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

                    <span class="mw-upbox-date-label">شروع توسعه</span>

                    <b>{{ $startedAt }}</b>
                </div>
            @endif

            @if($releaseAt)
                <div class="mw-upbox-date">
                    <span class="mw-upbox-date-icon" aria-hidden="true">
                        @include('icons.calendar-03')
                    </span>

                    <span class="mw-upbox-date-label">تاریخ انتشار</span>

                    <b>{{ $releaseAt }}</b>
                </div>
            @endif
        </div>

        <div class="mw-upbox-foot {{ $isPublished ? 'is-done' : '' }}">
            @if($isPublished)
                <span class="mw-upbox-foot-check" aria-hidden="true">
                    @include('icons.checkmark-badge-01')
                </span>

                آپدیت منتشر شد
            @else
                <span class="mw-upbox-dots" aria-hidden="true">
                    <i></i><i></i><i></i>
                </span>

                آپدیت در مسیر انتشار
            @endif
        </div>
    </section>
@endif
