{{--
    بلوک ترکیب تیم از دیتابیس بازیکنان پچ — {type: squad}
    دیتا از GetPglSquadAction می‌آید (تزریق خودکار وقتی pgl_team_id در کاتالوگ ست شده باشد).
    squad / changes (groupBy pid) / newPids / latestImport — نام‌ها دوزبانه (name_fa/name).
--}}
@php($locale = $locale ?? 'fa')
@php($isFa = $locale !== 'en')
<div class="reveal">
    <div class="mx-auto mb-8 max-w-2xl text-center">
        <h3 class="text-2xl font-black text-ink">{{ $isFa ? 'ترکیب تیم در پچ' : 'Squad in the patch' }}</h3>
        <p class="mt-3 text-sm leading-7 text-muted">
            {{ $isFa ? 'مستقیم از دیتابیس پچ؛ با هر آپدیت، انتقال‌ها و تغییر اورال/پست خودکار اعمال می‌شود.' : 'Straight from the patch database; transfers and rating/position changes apply automatically with every update.' }}
            @if(!empty($block['latestImport']))
                {{ $isFa ? 'آخرین به‌روزرسانی:' : 'Last update:' }}
                <span class="font-bold text-accent">{{ $block['latestImport']->label ?: ('#'.$block['latestImport']->id) }}</span>
            @endif
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-edge/60 bg-surface/50">
        {{-- هدر جدول --}}
        <div class="hidden grid-cols-[3rem_1fr_5.5rem_5rem_4.5rem] gap-2 border-b border-edge/60 bg-surface px-4 py-3 text-[11px] font-extrabold uppercase tracking-wider text-muted sm:grid">
            <span>#</span>
            <span>{{ $isFa ? 'بازیکن' : 'Player' }}</span>
            <span>{{ $isFa ? 'پست' : 'Pos' }}</span>
            <span class="text-center">{{ $isFa ? 'اورال' : 'OVR' }}</span>
            <span class="text-center">{{ $isFa ? 'سن' : 'Age' }}</span>
        </div>

        @foreach($block['squad'] as $p)
            @php($ch = $block['changes'][$p->pid] ?? collect())
            <a href="{{ route('players.player', $p->pid) }}"
               class="group grid grid-cols-[3rem_1fr_5.5rem] items-center gap-2 border-b border-edge/40 px-4 py-3 transition-colors last:border-0 hover:bg-surface sm:grid-cols-[3rem_1fr_5.5rem_5rem_4.5rem]">
                <span class="font-num text-sm font-bold text-muted">{{ $loop->iteration }}</span>

                <span class="min-w-0">
                    <span class="flex flex-wrap items-center gap-1.5">
                        <span class="truncate text-sm font-extrabold text-ink transition-colors group-hover:text-accent">
                            {{ $isFa ? ($p->name_fa ?: $p->name) : $p->name }}
                        </span>
                        @if($isFa && $p->name_fa && $p->name)
                            <span class="truncate text-[11px] text-muted/70">{{ $p->name }}</span>
                        @endif
                        @if(isset($block['newPids'][$p->pid]))
                            <span class="rounded-md bg-accent/15 px-1.5 py-0.5 text-[10px] font-black text-accent">{{ $isFa ? 'جدید' : 'NEW' }}</span>
                        @endif
                    </span>
                    <span class="mt-1 flex flex-wrap items-center gap-1.5">
                        @foreach($ch as $c)
                            @if($c->type === 'overall')
                                @php([$from, $to] = $c->payload['overall'])
                                <span class="rounded-md px-1.5 py-0.5 text-[10px] font-black {{ $to > $from ? 'bg-emerald-500/15 text-emerald-400' : 'bg-rose-500/15 text-rose-400' }}">
                                    {{ $from }} {{ $to > $from ? '▲' : '▼' }} {{ $to }}
                                </span>
                            @elseif($c->type === 'transfer')
                                <span class="rounded-md bg-accent/10 px-1.5 py-0.5 text-[10px] font-bold text-accent">{{ $isFa ? 'انتقال' : 'TRANSFER' }}</span>
                            @elseif($c->type === 'position')
                                <span class="rounded-md bg-sky-500/10 px-1.5 py-0.5 text-[10px] font-bold text-sky-400">
                                    {{ $isFa ? 'پست' : 'POS' }} {{ $c->payload['position'][0] }} → {{ $c->payload['position'][1] }}
                                </span>
                            @endif
                        @endforeach
                    </span>
                </span>

                <span class="text-xs font-bold text-muted">{{ $isFa ? ($p->pos_fa ?: $p->pos) : $p->pos }}</span>

                <span class="hidden text-center sm:block">
                    <span class="font-num inline-grid size-9 place-items-center rounded-xl border border-accent/20 bg-accent/10 text-sm font-black text-accent">
                        {{ $p->overall }}
                    </span>
                </span>

                <span class="font-num hidden text-center text-sm font-bold text-muted sm:block">{{ $p->age }}</span>
            </a>
        @endforeach
    </div>

    <p class="mt-4 text-center text-xs text-muted">
        {{ $isFa ? 'برای آمار کامل و تاریخچه‌ی تغییرات هر بازیکن، روی نامش کلیک کنید.' : 'Click a player for full stats and change history.' }}
    </p>
</div>
