@props([
    'target',
    'label' => 'زمان باقی مانده',
    'compact' => false,
])

@if($target)
    <div
        x-data="{
            target: new Date(@js($target->toIso8601String())).getTime(),
            now: Date.now(),
            timer: null,
            tick() { this.now = Date.now() },
            part(unit) {
                const diff = Math.max(0, this.target - this.now);
                const totalSeconds = Math.floor(diff / 1000);
                if (unit === 'days') return Math.floor(totalSeconds / 86400);
                if (unit === 'hours') return Math.floor((totalSeconds % 86400) / 3600);
                if (unit === 'minutes') return Math.floor((totalSeconds % 3600) / 60);
                return totalSeconds % 60;
            },
            pad(value) { return String(value).padStart(2, '0') },
            get finished() { return this.target <= this.now }
        }"
        x-init="tick(); timer = setInterval(() => tick(), 1000)"
        class="{{ $compact ? '' : 'rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur-md dark:bg-slate-950/20' }}"
    >
        <div class="flex items-center justify-between gap-3">
            <div class="text-[11px] font-extrabold {{ $compact ? 'text-slate-500 dark:text-slate-400' : 'text-white/70' }}">
                {{ $label }}
            </div>

            <span
                x-show="finished"
                x-cloak
                class="rounded-lg px-2 py-1 text-[10px] font-black {{ $compact ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' : 'bg-white/10 text-white/80' }}"
            >
                پایان یافته
            </span>
        </div>

        <div class="mt-3 grid grid-cols-4 gap-1.5 sm:gap-2" dir="ltr">
            @foreach([
                ['key' => 'days', 'label' => 'روز'],
                ['key' => 'hours', 'label' => 'ساعت'],
                ['key' => 'minutes', 'label' => 'دقیقه'],
                ['key' => 'seconds', 'label' => 'ثانیه'],
            ] as $part)
                <div class="rounded-2xl border px-1.5 py-2 text-center {{ $compact ? 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/70' : 'border-white/10 bg-slate-950/15' }}">
                    <div
                        class="text-sm font-black tabular-nums sm:text-base {{ $compact ? 'text-slate-900 dark:text-white' : 'text-white' }}"
                        x-text="pad(part('{{ $part['key'] }}'))"
                    >00</div>
                    <div class="mt-0.5 text-[9px] font-bold {{ $compact ? 'text-slate-400 dark:text-slate-500' : 'text-white/55' }}">
                        {{ $part['label'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
