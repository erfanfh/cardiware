@extends('front.master')

@section('head')
    <title>بازیکنان {{ $team->displayName('fa') }} | {{ get_setting('general', 'title') }}</title>
    <meta name="description" content="ترکیب و اورال بازیکنان {{ $team->displayName('fa') }} در پچ PGL با نشان تغییرات آخرین آپدیت">
@endsection

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-6 sm:py-8 space-y-6">
        <nav class="text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('players.hub') }}" class="hover:text-emerald-600">بازیکنان پچ PGL</a>
            <span class="mx-1">/</span>
            <span>{{ $team->displayName('fa') }}</span>
        </nav>

        <header class="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $team->displayName('fa') }}</h1>
                @if($team->name_fa && $team->name)
                    <p class="mt-0.5 text-xs text-slate-400">{{ $team->name }}</p>
                @endif
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $squad->count() }} بازیکن
                    @if($latestImport && $changes->count())
                        — {{ $changes->count() }} تغییر در آخرین آپدیت
                        ({{ $latestImport->label ?: ('ایمپورت #'.$latestImport->id) }})
                    @endif
                </p>
            </div>
            @if($team->code)
                <span class="rounded-2xl bg-slate-100 px-4 py-2 text-lg font-black tracking-widest text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $team->code }}</span>
            @endif
        </header>

        <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                <tr class="border-b border-slate-200 text-right text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">بازیکن</th>
                    <th class="px-4 py-3">پست</th>
                    <th class="px-4 py-3">اورال</th>
                    <th class="px-4 py-3">سن</th>
                    <th class="px-4 py-3">قد</th>
                    <th class="px-4 py-3">آخرین تغییر</th>
                </tr>
                </thead>
                <tbody>
                @foreach($squad as $i => $p)
                    @php
                        $rowChanges = $changes->get($p->pid, collect());
                        $ovrChange = $rowChanges->firstWhere('type', 'overall');
                        $trChange = $rowChanges->firstWhere('type', 'transfer');
                    @endphp
                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 dark:border-slate-800/60 dark:hover:bg-slate-800/40">
                        <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('players.player', $p->pid) }}" class="font-bold text-slate-800 hover:text-emerald-600 dark:text-slate-200">{{ $p->displayName('fa') }}</a>
                            @if($p->name_fa && $p->name)
                                <span class="ms-1 text-[11px] text-slate-400">{{ $p->name }}</span>
                            @endif
                            @if(isset($newPids[$p->pid]))
                                <span class="ms-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">جدید</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $p->pos_fa ?: $p->pos }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 font-black text-slate-900 dark:text-white">{{ $p->overall }}</span>
                            @if($ovrChange)
                                @php [$from, $to] = $ovrChange->payload['overall']; @endphp
                                <span class="text-[11px] font-bold {{ $to > $from ? 'text-emerald-600' : 'text-rose-600' }}">
                                    ({{ $from }} {{ $to > $from ? '▲' : '▼' }} {{ $to }})
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $p->age ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $p->height ? $p->height.' cm' : '—' }}</td>
                        <td class="px-4 py-3">
                            @if($trChange)
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-black text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">بازه‌افزایی</span>
                            @elseif($ovrChange)
                                <span class="text-[11px] text-slate-400">تغییر اورال</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
