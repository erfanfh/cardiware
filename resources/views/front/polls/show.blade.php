@extends('front.master')

@php
    use App\Domain\Poll\Enums\PollAudienceEnum;
    $selectedIds = old('option_ids', $response?->options?->pluck('id')->all() ?? []);
    $canSubmit = $poll->acceptsVotesAt()
        && !($poll->audience === PollAudienceEnum::MEMBERS && !auth()->check())
        && (!$hasVoted || $poll->allow_vote_changes);
    $statusLabel = !$poll->acceptsVotesAt()
        ? ($poll->starts_at?->isFuture() ? 'به‌زودی آغاز می‌شود' : 'پایان یافته')
        : 'در حال برگزاری';
@endphp

@section('content')
    <main class="relative min-h-[70vh] overflow-hidden">
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-[540px] bg-[radial-gradient(circle_at_20%_15%,rgba(249,115,22,.13),transparent_32%),radial-gradient(circle_at_80%_5%,rgba(14,165,233,.12),transparent_30%)] dark:bg-[radial-gradient(circle_at_20%_15%,rgba(249,115,22,.09),transparent_32%),radial-gradient(circle_at_80%_5%,rgba(14,165,233,.08),transparent_30%)]"></div>

        <div class="relative mx-auto max-w-5xl px-4 py-7 sm:py-10 lg:py-14">
            <nav class="mb-5 flex items-center gap-2 text-[11px] font-bold text-slate-400 sm:text-xs"><a
                    href="{{ route('home') }}" class="transition hover:text-brand-600">خانه</a><span>—</span><a
                    class="transition hover:text-brand-600" href="{{ route('polls.index') }}">نظرسنجی‌ها</a><span>—</span><span
                    class="max-w-[250px] truncate text-slate-600 dark:text-slate-300">{{ $poll->title }}</span></nav>

            <section
                class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_25px_80px_-40px_rgba(15,23,42,.35)] dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/30 sm:rounded-[36px]">
                <header
                    class="relative overflow-hidden border-b border-slate-100 px-5 py-6 dark:border-slate-800 sm:px-8 sm:py-8 lg:px-10">
                    <div
                        class="absolute inset-0 bg-gradient-to-l from-brand-50/80 via-white to-sky-50/50 dark:from-brand-950/30 dark:via-slate-900 dark:to-sky-950/20"></div>
                    <div
                        class="absolute -left-16 -top-20 h-52 w-52 rounded-full bg-orange-200/35 blur-3xl dark:bg-orange-500/10"></div>
                    <div class="relative">
                        <div class="flex flex-wrap items-center gap-2">
                            <span @class(['inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-black ring-1 ring-inset', 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20' => $poll->acceptsVotesAt(), 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700' => !$poll->acceptsVotesAt()])><span
                                    class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $statusLabel }}</span>
                            <span
                                class="rounded-full bg-white/80 px-3 py-1.5 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200 dark:bg-slate-800/80 dark:text-slate-300 dark:ring-slate-700">{{ $poll->audience->label() }}</span>
                            @if($poll->allows_multiple_selections)
                                <span
                                    class="rounded-full bg-violet-50 px-3 py-1.5 text-[11px] font-bold text-violet-700 ring-1 ring-violet-200 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/20">چندگزینه‌ای</span>
                            @endif
                        </div>
                        <h1 class="mt-5 text-2xl font-black leading-[1.9] text-slate-900 dark:text-white sm:text-3xl">{{ $poll->title }}</h1>
                        <div
                            class="tinymce-content mt-4 text-base font-bold leading-9 text-slate-700 dark:text-slate-200 sm:text-lg">{!! $poll->question !!}</div>
                        @if($poll->description)
                            <div
                                class="tinymce-content mt-5 border-r-2 border-brand-300 pr-4 text-sm leading-8 text-slate-600 dark:border-brand-500 dark:text-slate-300">{!! $poll->description !!}</div>
                        @endif
                    </div>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_270px]">
                    <div class="p-5 sm:p-8 lg:p-10">
                        @if(session('success'))
                            <div
                                class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold leading-7 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                                <span
                                    class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">✓</span>{{ session('success') }}
                            </div>
                        @endif
                        @if($errors->any())
                            <div
                                class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm leading-7 text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">{{ $errors->first() }}</div>
                        @endif

                        @if($canSubmit)
                            <form method="post" action="{{ route('polls.vote', $poll->slug) }}"
                                  x-data="{ selected: @js(array_map('intval', $selectedIds)), multiple: @js($poll->allows_multiple_selections), max: {{ $poll->max_selections }} }"
                                  @submit="if (!selected.length) { $event.preventDefault(); alert('لطفاً یک گزینه را انتخاب کنید.'); }">
                                @csrf
                                <div class="mb-4 flex items-center justify-between gap-3"><h2
                                        class="text-sm font-black text-slate-900 dark:text-white">انتخاب
                                        شما</h2>@if($poll->allows_multiple_selections)
                                        <span class="text-[11px] font-bold text-slate-400">حداقل {{ $poll->min_selections }} و حداکثر {{ $poll->max_selections }} گزینه</span>
                                    @else
                                        <span
                                            class="text-[11px] font-bold text-slate-400">یک گزینه را انتخاب کنید</span>
                                    @endif</div>
                                <div class="space-y-3">
                                    @foreach($options as $option)
                                        <label class="group relative block cursor-pointer">
                                            <input
                                                type="{{ $poll->allows_multiple_selections ? 'checkbox' : 'radio' }}"
                                                name="option_ids[]" value="{{ $option->id }}"
                                                @checked(in_array($option->id, array_map('intval', $selectedIds), true))
                                                @change="if (multiple) { const id = Number($event.target.value); if ($event.target.checked && selected.length >= max) { $event.target.checked = false; return; } selected = $event.target.checked ? [...selected, id] : selected.filter(v => v !== id); } else { selected = [Number($event.target.value)]; }"
                                                class="peer sr-only"
                                            >
                                            <span
                                                class="flex min-h-[64px] items-center gap-4 rounded-2xl border border-slate-200 bg-white p-3.5 transition duration-200 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md peer-checked:border-brand-400 peer-checked:bg-brand-50/70 peer-checked:ring-2 peer-checked:ring-brand-100 dark:border-slate-700 dark:bg-slate-800/60 dark:hover:border-brand-500/40 dark:peer-checked:border-brand-500 dark:peer-checked:bg-brand-500/10 dark:peer-checked:ring-brand-500/10 sm:p-4">
                                                <span
                                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 border-slate-300 bg-white transition peer-checked:border-brand-500 dark:border-slate-600 dark:bg-slate-900"><span
                                                        class="h-2.5 w-2.5 rounded-full opacity-0 transition group-has-[:checked]:opacity-100"
                                                        style="background-color: {{ $option->color ?: '#f97316' }}"></span></span>
                                                <span class="min-w-0 flex-1"><span
                                                        class="block text-sm font-black text-slate-800 dark:text-slate-100 sm:text-[15px]">{{ $option->label }}</span>@if($option->description)
                                                        <span
                                                            class="mt-1 block text-xs leading-6 text-slate-400">{{ $option->description }}</span>
                                                    @endif</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit"
                                        class="cursor-pointer mt-5 flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-l from-brand-500 to-orange-500 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-200/60 transition hover:-translate-y-0.5 dark:shadow-none">{{ $hasVoted ? 'به‌روزرسانی رأی' : 'ثبت رأی' }}
                                    <span aria-hidden="true">←</span></button>
                            </form>
                        @elseif($poll->audience === PollAudienceEnum::MEMBERS && !auth()->check())
                            <div
                                class="rounded-3xl border border-sky-200 bg-sky-50 p-6 text-center dark:border-sky-500/20 dark:bg-sky-500/10">
                                <div
                                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-500 text-2xl text-white">
                                    👤
                                </div>
                                <h2 class="mt-4 text-base font-black text-slate-900 dark:text-white">ویژه اعضای
                                    مودینگ‌وی</h2>
                                <p class="mt-2 text-xs leading-7 text-slate-500 dark:text-slate-300">برای ثبت رأی، ابتدا
                                    وارد حساب کاربری خود شوید.</p><a href="{{ route('login') }}"
                                                                     class="mt-4 inline-flex rounded-2xl bg-sky-600 px-6 py-3 text-xs font-black text-white transition hover:bg-sky-700">ورود
                                    به حساب کاربری</a></div>
                        @elseif($hasVoted && !$poll->allow_vote_changes)
                            <div
                                class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 text-center dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <div
                                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500 text-2xl font-black text-white">
                                    ✓
                                </div>
                                <h2 class="mt-4 text-base font-black text-slate-900 dark:text-white">رأی شما ثبت شده
                                    است</h2>
                                <p class="mt-2 text-xs leading-7 text-slate-500 dark:text-slate-300">از وقتی که برای
                                    مشارکت در این نظرسنجی گذاشتید ممنونیم.</p></div>
                        @else
                            <div
                                class="rounded-3xl border border-slate-200 bg-slate-50 p-6 text-center dark:border-slate-700 dark:bg-slate-800/50">
                                <div class="text-3xl">⌛</div>
                                <h2 class="mt-3 text-base font-black text-slate-900 dark:text-white">امکان ثبت رأی وجود
                                    ندارد</h2>
                                <p class="mt-2 text-xs leading-7 text-slate-500 dark:text-slate-300">{{ $poll->starts_at?->isFuture() ? 'این نظرسنجی هنوز شروع نشده است.' : 'مهلت شرکت در این نظرسنجی به پایان رسیده است.' }}</p>
                            </div>
                        @endif

                        @if($showResults && $results)
                            <section class="mt-8 border-t border-slate-100 pt-7 dark:border-slate-800">
                                <div class="mb-5 flex items-end justify-between gap-4">
                                    <div><h2 class="text-base font-black text-slate-900 dark:text-white">نتایج
                                            لحظه‌ای</h2>
                                        <p class="mt-1 text-[11px] text-slate-400">درصد بر اساس تعداد پاسخ‌دهندگان</p>
                                    </div>@if($poll->show_response_count)
                                        <div class="text-left">
                                            <div
                                                class="text-xl font-black text-brand-600 dark:text-brand-400">{{ number_format($results['responses']) }}</div>
                                            <div class="text-[10px] text-slate-400">پاسخ معتبر</div>
                                        </div>
                                    @endif</div>
                                <div class="space-y-5">
                                    @foreach($results['options'] as $result)
                                        @php($isMine = $response?->options?->contains('id', $result['id']))
                                        <div>
                                            <div class="mb-2 flex items-start justify-between gap-4">
                                                <div
                                                    class="text-sm font-extrabold text-slate-700 dark:text-slate-200">{{ $result['label'] }} @if($isMine)
                                                        <span
                                                            class="mr-1 text-[10px] text-brand-600 dark:text-brand-400">انتخاب شما</span>
                                                    @endif</div>
                                                <div
                                                    class="shrink-0 text-xs font-black text-slate-600 dark:text-slate-300">{{ number_format($result['respondent_percentage'], 1) }}
                                                    ٪ @if($poll->show_response_count)
                                                        <span class="font-normal text-slate-400">({{ number_format($result['count']) }})</span>
                                                    @endif</div>
                                            </div>
                                            <div
                                                class="h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div class="h-full rounded-full transition-all duration-700"
                                                     style="width: {{ min(100, $result['respondent_percentage']) }}%; background-color: {{ $result['color'] }}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>

                    <aside
                        class="border-t border-slate-100 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/35 lg:border-r lg:border-t-0 sm:p-6">
                        <h2 class="text-xs font-black text-slate-900 dark:text-white">اطلاعات نظرسنجی</h2>
                        <div class="mt-4 divide-y divide-slate-200/70 text-xs dark:divide-slate-800">
                            @foreach([
                                ['label' => 'شروع', 'value' => $poll->starts_at ? jalali_date($poll->starts_at, 'Y/m/d H:i') : 'از زمان انتشار'],
                                ['label' => 'پایان', 'value' => $poll->ends_at ? jalali_date($poll->ends_at, 'Y/m/d H:i') : 'بدون محدودیت'],
                                ['label' => 'نوع انتخاب', 'value' => $poll->allows_multiple_selections ? 'چندگزینه‌ای' : 'تک‌گزینه‌ای'],
                                ['label' => 'مخاطب', 'value' => $poll->audience->label()],
                            ] as $item)
                                <div class="flex items-center justify-between gap-3 py-3"><span
                                        class="text-slate-400">{{ $item['label'] }}</span><span
                                        class="text-left font-extrabold text-slate-700 dark:text-slate-300">{{ $item['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </aside>
                </div>
            </section>
        </div>
    </main>
@endsection
