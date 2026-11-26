@extends('front.master')

@section('head')
    <title>{{ $player->name }} | Patch players PGL | {{ get_setting('general', 'title') }}</title>
    <meta name="description" content="{{ $player->name }} — Overall rating {{ $player->overall }}, {{ $player->pos }}, complete stats and patch change history PGL">
@endsection

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-6 sm:py-8 space-y-6">
        <nav class="text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('players.hub') }}" class="hover:text-emerald-600">Patch players PGL</a>
            @foreach($player->teams->take(2) as $t)
                <span class="mx-1">/</span>
                <a href="{{ route('players.team', $t->id) }}" class="hover:text-emerald-600">{{ $t->name }}</a>
            @endforeach
            <span class="mx-1">/</span>
            <span>{{ $player->name }}</span>
        </nav>

        <header class="flex flex-wrap items-center gap-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex size-24 shrink-0 flex-col items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md">
                <span class="text-3xl font-black">{{ $player->overall }}</span>
                <span class="text-[10px] font-bold opacity-80">Overall rating</span>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $player->displayName('en') }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $player->pos }}
                    @if($player->age) • {{ $player->age }} Year @endif
                    @if($player->height) • {{ $player->height }} cm @endif
                    @if($player->weight) • {{ $player->weight }} kg @endif
                    @if($player->shirt) • Kit: {{ $player->shirt }} @endif
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($player->teams as $t)
                        <a href="{{ route('players.team', $t->id) }}"
                           class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 hover:bg-emerald-100 hover:text-emerald-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ $t->displayName('en') }}
                        </a>
                    @endforeach
                </div>
            </div>
        </header>

        @if($changes->isNotEmpty())
            <section class="space-y-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Change history</h2>
                <ol class="space-y-2">
                    @foreach($changes as $ch)
                        <li class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-800 dark:bg-slate-800/50">
                            <span class="rounded-full bg-slate-200 px-2.5 py-0.5 text-[11px] font-black text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                {{ $ch->import->label ?: ('Update #'.$ch->import_id) }}
                            </span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ \Illuminate\Support\Str::headline($ch->type) }}</span>
                            @if($ch->type === 'overall')
                                @php [$from, $to] = $ch->payload['overall']; @endphp
                                <span class="font-black {{ $to > $from ? 'text-emerald-600' : 'text-rose-600' }}">{{ $from }} → {{ $to }} {{ $to > $from ? '▲' : '▼' }}</span>
                            @elseif($ch->type === 'transfer')
                                <span class="text-slate-600 dark:text-slate-400">
                                    {{ collect($ch->payload['transfer']['from'] ?? [])->implode(', ') ?: '—' }}
                                    →
                                    <b class="text-slate-800 dark:text-slate-200">{{ collect($ch->payload['transfer']['to'] ?? [])->implode(', ') ?: '—' }}</b>
                                </span>
                            @elseif($ch->type === 'stats')
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ collect($ch->payload['stats'] ?? [])->take(3)->map(fn($d) => ($d[0] > $d[1] ? '' : '+').($d[1]-$d[0]))->implode(' / ') }}
                                    in {{ count($ch->payload['stats'] ?? []) }} Statistics
                                </span>
                            @else
                                <span class="text-slate-600 dark:text-slate-400">{{ json_encode($ch->payload[$ch->type] ?? $ch->payload, JSON_UNESCAPED_UNICODE) }}</span>
                            @endif
                            <span class="ms-auto text-[11px] text-slate-400">{{ \Illuminate\Support\Carbon::parse($ch->created_at)->format('j F Y') }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <section class="space-y-5">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Statistics</h2>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($player->statsByGroup() as $group => $rows)
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="mb-3 text-sm font-black text-emerald-700 dark:text-emerald-400">{{ $group }}</h3>
                        <div class="space-y-2.5">
                            @foreach($rows as $s)
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-xs">
                                        <span class="text-slate-600 dark:text-slate-400">{{ $s['label'] }}</span>
                                        <span class="font-black text-slate-800 dark:text-slate-200">{{ $s['value'] }}</span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-l from-emerald-500 to-teal-400"
                                             style="width: {{ min(100, max(0, ($s['value'] - 40) * 1.67)) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </section>
@endsection
