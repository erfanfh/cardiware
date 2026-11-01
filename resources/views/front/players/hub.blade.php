@extends('front.master')

@section('head')
    <title>بازیکنان پچ PGL | {{ get_setting('general', 'title') }}</title>
    <meta name="description" content="اطلاعات کامل بازیکنان پچ PGL به تفکیک تیم — اورال، پست، آمار و تغییرات هر آپدیت">
@endsection

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-6 sm:py-8 space-y-8">
        <header class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white">بازیکنان پچ PGL</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 dark:text-slate-400">
                        اطلاعات و اورال بازیکنان مستقیم از دیتابیس پچ خوانده می‌شود؛ با هر آپدیت پچ،
                        تغییرات (اورال، پست، انتقال و آمار) روی صفحه‌ی هر بازیکن و تیم نمایش داده می‌شود.
                        @if($latestImport)
                            آخرین به‌روزرسانی:
                            {{ $latestImport->label ?: ('ایمپورت #'.$latestImport->id) }}
                            — {{ jdate($latestImport->created_at)->format('j F Y') }}.
                        @endif
                    </p>
                </div>
                <form action="{{ route('players.hub') }}" method="GET" class="flex w-full max-w-sm gap-2">
                    <input type="search" name="q" value="{{ $q }}" placeholder="جستجوی بازیکن… (مثلاً Taremi)"
                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-emerald-400 dark:border-slate-700 dark:bg-slate-800">
                    <button type="submit" class="shrink-0 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-emerald-700">جستجو</button>
                </form>
            </div>
        </header>

        @if($q !== '' && $searchResults->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                    نتیجه‌ی جستجو برای «{{ $q }}» ({{ $searchResults->count() }})
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach($searchResults as $p)
                        <a href="{{ route('players.player', $p->pid) }}"
                           class="group rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm transition hover:border-emerald-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                            <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-slate-800 text-sm font-black text-white dark:bg-slate-700">
                                {{ $p->overall ?? '—' }}
                            </div>
                            <div class="mt-2 truncate text-sm font-bold text-slate-800 group-hover:text-emerald-600 dark:text-slate-200">{{ $p->displayName('fa') }}</div>
                            <div class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $p->pos_fa ?: $p->pos }} • {{ $p->teams->first()?->displayName('fa') }}</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @elseif($q !== '')
            <p class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-400 dark:border-slate-700">
                بازیکنی با این نام پیدا نشد.
            </p>
        @endif

        @if($topPlayers->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">ستاره‌های پچ</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach($topPlayers as $p)
                        <a href="{{ route('players.player', $p->pid) }}"
                           class="group rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm transition hover:border-emerald-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                            <div class="mx-auto flex size-12 items-center justify-center rounded-full
                                        bg-gradient-to-br from-emerald-500 to-teal-600 text-lg font-black text-white">
                                {{ $p->overall }}
                            </div>
                            <div class="mt-2 truncate text-sm font-bold text-slate-800 group-hover:text-emerald-600 dark:text-slate-200">{{ $p->displayName('fa') }}</div>
                            <div class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $p->teams->first()?->displayName('fa') }}</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($iranianTeams->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">تیم‌های ایرانی</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($iranianTeams as $t)
                        <a href="{{ route('players.team', $t->id) }}"
                           class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                            {{ $t->displayName('fa') }}
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($latestChanges->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">تغییرات آخرین آپدیت</h2>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($latestChanges as $ch)
                        <a href="{{ route('players.player', $ch->pid) }}"
                           class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition hover:border-emerald-300 dark:border-slate-800 dark:bg-slate-900">
                            <span class="truncate font-bold text-slate-800 dark:text-slate-200">{{ $ch->name }}</span>
                            @if($ch->type === 'overall')
                                @php [$from, $to] = $ch->payload['overall']; @endphp
                                <span class="shrink-0 font-black {{ $to > $from ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $from }} → {{ $to }} {{ $to > $from ? '▲' : '▼' }}
                                </span>
                            @elseif($ch->type === 'transfer')
                                <span class="shrink-0 text-xs text-slate-500 dark:text-slate-400">
                                    {{ collect($ch->payload['transfer']['to'] ?? [])->first() ?: '—' }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">همه تیم‌ها ({{ number_format($teams->count()) }})</h2>
                <input id="pgl-team-search" type="search" placeholder="جستجوی تیم…"
                       class="w-full max-w-xs rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm outline-none focus:border-emerald-400 dark:border-slate-700 dark:bg-slate-900">
            </div>
            <div id="pgl-team-grid" class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach($teams as $t)
                    <a href="{{ route('players.team', $t->id) }}" data-name="{{ mb_strtolower($t->name.' '.($t->name_fa ?: '')) }}"
                       class="truncate rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-center text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                        {{ $t->name_fa ?: $t->name }}
                    </a>
                @endforeach
            </div>
        </section>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('pgl-team-search');
            var cards = document.querySelectorAll('#pgl-team-grid a');
            input.addEventListener('input', function () {
                var q = this.value.trim().toLowerCase();
                cards.forEach(function (a) {
                    a.style.display = (!q || a.dataset.name.indexOf(q) !== -1) ? '' : 'none';
                });
            });
        });
    </script>
@endsection
