@php use App\Domain\Project\Enums\RoadmapItemStatusEnum; @endphp
@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8 pb-28 lg:pb-8" x-data="projectPage()">
        <section
            class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-xl dark:border-slate-800 dark:shadow-black/30">
            @if($project->cover)
                <img src="{{ $project->cover->url }}" alt="{{ $project->title }}" title="{{ $project->title }}"
                     class="absolute inset-0 h-full w-full object-cover opacity-80"/>
            @endif

            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/75 via-slate-950/35 to-slate-950/10"></div>

            <div class="relative p-6 sm:p-10">
                <nav class="text-xs text-white/70 flex flex-wrap gap-2">
                    <a href="{{ route('home') }}" class="hover:text-brand-300 font-bold transition">
                        خانه
                    </a>
                    <span>—</span>
                    <a href="{{ front_route('projects.list') }}" class="hover:text-brand-300 font-bold transition">
                        پروژه‌ها
                    </a>
                    <span>—</span>
                    <span class="text-white/90 font-semibold">{{ $project->title }}</span>
                </nav>

                <div class="mt-4 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                    <div class="max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($project->tags as $tag)
                                <span
                                    class="text-[11px] font-extrabold text-white/90 bg-white/10 border border-white/10 px-2 py-1 rounded-xl backdrop-blur">
                                    {{ $tag->title }}
                                </span>
                            @endforeach
                        </div>

                        <h1 class="mt-4 text-2xl sm:text-3xl font-black leading-tight text-white">
                            {{ $project->title }}
                        </h1>

                        <p class="mt-4 text-white/80 text-sm sm:text-base leading-7">
                            {{ $project->excerpt() }}
                        </p>

                        <div class="mt-6 flex flex-wrap items-center gap-2 text-xs text-white/70">
                            @if($project->last_updated_at)
                                <span
                                    class="inline-flex items-center gap-2 rounded-2xl bg-white/10 border border-white/10 px-3 py-2 backdrop-blur">
                                    <span class="h-2 w-2 rounded bg-brand-400"></span>
                                    <span>آخرین آپدیت:</span>
                                    <span class="font-extrabold text-white">
                                        {{ jalali_date($project->last_updated_at, 'd F Y') }}
                                    </span>
                                </span>
                            @endif

                            @if($project->current_version)
                                <span
                                    class="inline-flex items-center gap-2 rounded-2xl bg-white/10 border border-white/10 px-3 py-2 backdrop-blur">
                                    <span>نسخه فعلی:</span>
                                    <span class="font-extrabold text-white">{{ $project->current_version }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="w-full lg:w-[420px] rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-5">
                        <div class="flex items-center justify-between">
                            <div class="text-white/80 text-sm font-extrabold">درصد پیشرفت</div>
                            <div class="text-2xl font-black text-white">{{ $project->progress_percent }}%</div>
                        </div>
                        <div class="mt-3 h-2 rounded-full bg-white/15 overflow-hidden">
                            <div
                                class="h-full @if($project->progress_percent == 100) bg-emerald-500 @else bg-brand-400 @endif"
                                style="width:{{ $project->progress_percent }}%"></div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                                <div class="text-[11px] font-extrabold text-white/70">تسک‌ها</div>
                                <div class="mt-1 text-white font-black">{{ $project->roadmapItems->count() }}</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                                <div class="text-[11px] font-extrabold text-white/70">آپدیت‌ها</div>
                                <div class="mt-1 text-white font-black">{{ $project->changelogs->count() }}</div>
                            </div>
                            <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                                <div class="text-[11px] font-extrabold text-white/70">ویژگی‌ها</div>
                                <div class="mt-1 text-white font-black">{{ $project->features->count() }}</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6">

            <div class="lg:col-span-8 space-y-6">

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm relative overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                    <div
                        class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] opacity-80 dark:bg-brand-500/10"></div>
                    <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500 opacity-90"></div>

                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                            @include('icons.block-game', ['size' => 18])
                        </span>

                        <div>
                            <h2 class="text-base font-extrabold dark:text-slate-100">معرفی پروژه</h2>
                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">چه چیزی در این پروژه ساخته
                                می‌شود؟
                            </div>
                        </div>
                    </div>

                    <div class="tinymce-content mt-5 text-slate-700 dark:text-slate-200 dark:prose-invert">
                        {!! $project->body !!}
                    </div>

                    @if($project->features->count() > 0)
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($project->features as $feature)
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
                                    <h3 class="text-sm font-extrabold dark:text-slate-100">{{ $feature->title }}</h3>
                                    <div
                                        class="mt-1 text-xs text-slate-500 leading-6 dark:text-slate-400">{{ $feature->subtitle }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($project->roadmapItems->count() > 0)
                    <div id="roadmap"
                         class="scroll-mt-36 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2">
                                        <path d="M12 20l9-5-9-5-9 5 9 5z"></path>
                                        <path d="M12 12l9-5-9-5-9 5 9 5z"></path>
                                    </svg>
                                </span>

                                <div>
                                    <h2 class="text-base font-extrabold dark:text-slate-100">نقشه راه</h2>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">مرحله‌ها و وضعیت هر
                                        مرحله
                                    </div>
                                </div>
                            </div>

                            @if($project->last_updated_at)
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                <span>به‌روزرسانی:</span>
                                <span>{{ jalali_date($project->last_updated_at, 'd F Y') }}</span>
                            </span>
                            @endif
                        </div>

                        <div class="mt-5 space-y-3">
                            @php $counter = 1; @endphp
                            @foreach($project->roadmapItems as $item)
                                <div
                                    class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-700 dark:bg-slate-950">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-2xl border border-slate-200 bg-slate-50 grid place-items-center font-black text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                                {{ $counter }}
                                            </span>
                                                <h3
                                                    class="text-sm font-extrabold truncate dark:text-slate-100">{{ $item->title }}</h3>
                                            </div>
                                            <div class="mt-2 text-xs text-slate-500 leading-6 dark:text-slate-400">
                                                {!! nl2br(htmlspecialchars(e($item->excerpt))) !!}
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-extrabold px-2 py-1 rounded-xl border whitespace-nowrap
                                        @if($item->status === RoadmapItemStatusEnum::DONE) text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-300 dark:bg-emerald-500/10 dark:border-emerald-500/20
                                        @elseif($item->status === RoadmapItemStatusEnum::INPROGRESS) text-brand-800 bg-brand-50 border-brand-100 dark:text-brand-300 dark:bg-brand-500/10 dark:border-brand-500/20
                                        @else text-slate-700 bg-slate-100 border-slate-200 dark:text-slate-300 dark:bg-slate-800 dark:border-slate-700
                                        @endif">{{ $item->status->label() }}</span>
                                    </div>

                                    <div class="mt-4">
                                        <div
                                            class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>پیشرفت مرحله</span>
                                            <span class="font-extrabold text-slate-700 dark:text-slate-200">
                                                {{ $item->progress_percent }}%
                                            </span>
                                        </div>
                                        <div
                                            class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                                            <div
                                                class="h-full @if($item->status === RoadmapItemStatusEnum::DONE) bg-emerald-500 @else bg-brand-500 @endif"
                                                style="width:{{ $item->progress_percent }}%"></div>
                                        </div>
                                    </div>
                                    @php $counter++; @endphp
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($project->changelogs->count() > 0)
                    <div id="updates"
                         class="scroll-mt-36 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2">
                                    <path d="M21 12a9 9 0 1 1-2.64-6.36"></path>
                                    <path d="M21 3v6h-6"></path>
                                </svg>
                            </span>

                            <div>
                                <h2 class="text-base font-extrabold dark:text-slate-100">آپدیت‌ها و تغییرات</h2>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">تازه‌ترین اتفاقات پروژه
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach($project->changelogs as $item)
                                <div
                                    class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 hover:bg-slate-50 transition dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3
                                                class="text-sm font-extrabold dark:text-slate-100">{{ $item->title }}</h3>
                                            <div class="mt-2 text-sm text-slate-700 leading-7 dark:text-slate-300">
                                                {!! nl2br(htmlspecialchars(e($item->body))) !!}
                                            </div>
                                        </div>
                                        @if($item->published_at)
                                            <span
                                                class="text-xs text-slate-500 shrink-0 dark:text-slate-400">{{ time_ago($item->published_at)  }}</span>
                                        @endif
                                    </div>

                                    @if(count($item->tagsList()) > 0)
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($item->tagsList() as $tag)
                                                <span
                                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- SIDEBAR -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Key info -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2">
                              <path d="M12 20h9"></path>
                              <path d="M12 4h9"></path>
                              <path d="M4 9h16"></path>
                              <path d="M4 15h16"></path>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-extrabold dark:text-slate-100">جزئیات پروژه</h2>
                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">اطلاعات مهم پروژه</div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-300">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 dark:text-slate-400">وضعیت</span>
                            <span class="font-extrabold"
                                  :class="project.status==='done' ? 'text-emerald-700 dark:text-emerald-300' : 'text-brand-700 dark:text-brand-300'"
                            >{{ \App\Domain\Project\Enums\ProjectStatusEnum::from($project->status->value)->label() }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 dark:text-slate-400">پیشرفت</span>
                            <span class="font-extrabold">{{ $project->progress_percent . '%' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 dark:text-slate-400">نسخه فعلی</span>
                            <span class="font-extrabold">{{ $project->current_version }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 dark:text-slate-400">سازنده</span>
                            <span class="font-extrabold">{{ $project->creator_name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 dark:text-slate-400">پلتفرم</span>
                            <span class="font-extrabold">{{ $project->platform }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 dark:text-slate-400">آخـرین آپدیت</span>
                            <span class="font-extrabold">{{ jalali_date($project->last_updated_at, 'd F Y') }}</span>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <a href="#updates"
                           class="rounded-2xl bg-brand-500 px-4 py-3 text-sm font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition text-center dark:shadow-black/20 dark:hover:bg-brand-400">
                            مشاهده آپدیت‌ها
                        </a>
                        <a href="#roadmap"
                           class="rounded-2xl bg-white border border-slate-200 text-brand-700 px-4 py-3 text-sm font-extrabold hover:bg-brand-50 hover:border-brand-200 transition text-center dark:border-slate-700 dark:bg-slate-950 dark:text-brand-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10">
                            نقشه راه
                        </a>
                    </div>
                </div>

                @if($project->product_box_title || $project->product_box_subtitle || $project->product_box_button_text || $project->product_url)
                    <div
                        class="group relative overflow-hidden rounded-3xl border border-brand-300/40
                       bg-gradient-to-br from-brand-400 via-brand-500 to-orange-600
                       bg-[length:180%_180%] animate-[brandGradientFlow_7s_ease-in-out_infinite,heartbeat_2.2s_ease-in-out_infinite]
                       p-5 shadow-xl shadow-brand-500/25
                       dark:border-brand-400/25 dark:from-brand-500 dark:via-orange-600 dark:to-amber-700
                       dark:shadow-brand-900/40"
                    >
                        <div
                            class="absolute -top-10 -left-10 h-32 w-32 rounded-full bg-white/20 blur-2xl animate-[softPulse_3.5s_ease-in-out_infinite]"></div>
                        <div
                            class="absolute -bottom-12 right-4 h-32 w-32 rounded-full bg-yellow-300/25 blur-2xl animate-[softFloat_5s_ease-in-out_infinite]"></div>
                        <div
                            class="absolute inset-0 bg-[radial-gradient(circle_at_25%_15%,rgba(255,255,255,.28),transparent_28%),radial-gradient(circle_at_85%_75%,rgba(255,255,255,.16),transparent_30%)]"></div>

                        <div class="relative">
                            <div class="flex items-center gap-3">
                            <span
                                class="grid h-11 w-11 place-items-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20 backdrop-blur">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2">
                                    <path d="M7 17L17 7"></path>
                                    <path d="M8 7h9v9"></path>
                                </svg>
                            </span>

                                <div>
                                    <h2 class="text-base font-black text-white">
                                        {{ $project->product_box_title }}
                                    </h2>
                                    <p class="mt-1 text-xs font-semibold text-white/80">
                                        {{ $project->product_box_subtitle }}
                                    </p>
                                </div>
                            </div>

                            <a href="{{ $project->product_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="mt-5 flex items-center justify-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-black text-brand-700 shadow-lg transition
                              hover:-translate-y-0.5 hover:bg-orange-50 active:translate-y-0
                              dark:bg-white/95 dark:text-orange-700 dark:hover:bg-orange-50"
                            >
                                {{ $project->product_box_button_text }}
                                <span>←</span>
                            </a>
                        </div>
                    </div>
                @endif
            </aside>

        </section>

        @if($project->product_box_button_text || $project->product_url)
            <div
                class="fixed inset-x-0 bottom-0 z-40 border-t border-orange-200/40 bg-white/85 p-3 backdrop-blur-xl lg:hidden dark:border-orange-500/20 dark:bg-slate-950/85">
                <a href="{{ $project->product_url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="relative flex items-center justify-center gap-2 overflow-hidden rounded-2xl
                  bg-gradient-to-br from-brand-400 via-brand-500 to-orange-600
                  bg-[length:180%_180%] animate-[brandGradientFlow_6s_ease-in-out_infinite,heartbeat_2s_ease-in-out_infinite]
                  px-5 py-4 text-sm font-black text-white shadow-2xl shadow-brand-500/30 active:scale-[0.98]
                  dark:from-brand-500 dark:via-orange-600 dark:to-amber-700 dark:shadow-brand-900/50"
                >
                <span
                    class="absolute -left-8 top-1/2 h-24 w-24 -translate-y-1/2 rounded-full bg-white/20 blur-xl animate-[softPulse_3s_ease-in-out_infinite]"></span>
                    <span
                        class="absolute right-8 top-1/2 h-4 w-4 -translate-y-1/2 rounded-full bg-yellow-200/80 animate-[softPing_2s_ease-out_infinite]"></span>
                    <span
                        class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,.22),transparent_32%)]"></span>

                    <span class="relative">{{ $project->product_box_button_text }}</span>
                    <span class="relative">←</span>
                </a>
            </div>
        @endif
    </main>
@endsection
