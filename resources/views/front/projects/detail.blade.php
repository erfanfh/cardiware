@php use App\Domain\Project\Enums\RoadmapItemStatusEnum; @endphp
@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8" x-data="projectPage()">

        <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-xl">
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
                    <a href="{{ route('projects.list') }}" class="hover:text-brand-300 font-bold transition">
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

                <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm relative overflow-hidden">
                    <div
                        class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] opacity-80"></div>
                    <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500 opacity-90"></div>

                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                            @include('icons.block-game', ['size' => 18])
                        </span>

                        <div>
                            <div class="text-base font-extrabold">معرفی پروژه</div>
                            <div class="mt-1 text-xs text-slate-500">چه چیزی در این پروژه ساخته می‌شود؟</div>
                        </div>
                    </div>

                    <div class="tinymce-content mt-5 text-slate-700">
                        {!! $project->body !!}
                    </div>

                    @if($project->features->count() > 0)
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($project->features as $feature)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-sm font-extrabold">{{ $feature->title }}</div>
                                    <div class="mt-1 text-xs text-slate-500 leading-6">{{ $feature->subtitle }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($project->roadmapItems->count() > 0)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2">
                                    <path d="M12 20l9-5-9-5-9 5 9 5z"></path>
                                    <path d="M12 12l9-5-9-5-9 5 9 5z"></path>
                                </svg>
                            </span>

                                <div>
                                    <div class="text-base font-extrabold">نقشه راه</div>
                                    <div class="mt-1 text-xs text-slate-500">مرحله‌ها و وضعیت هر مرحله</div>
                                </div>
                            </div>

                            @if($project->last_updated_at)
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
                                <span>به‌روزرسانی:</span>
                                <span>{{ jalali_date($project->last_updated_at, 'd F Y') }}</span>
                            </span>
                            @endif
                        </div>

                        <div class="mt-5 space-y-3">
                            @php $counter = 1; @endphp
                            @foreach($project->roadmapItems as $item)
                                <div class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-2xl border border-slate-200 bg-slate-50 grid place-items-center font-black text-slate-700">
                                                {{ $counter }}
                                            </span>
                                                <div class="text-sm font-extrabold truncate">{{ $item->title }}</div>
                                            </div>
                                            <div class="mt-2 text-xs text-slate-500 leading-6">
                                                {!! nl2br(htmlspecialchars(e($item->excerpt))) !!}
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-extrabold px-2 py-1 rounded-xl border
                                        @if($item->status === RoadmapItemStatusEnum::DONE) text-emerald-700 bg-emerald-50 border-emerald-200
                                        @elseif($item->status === RoadmapItemStatusEnum::INPROGRESS) text-brand-800 bg-brand-50 border-brand-100
                                        @else text-slate-700 bg-slate-100 border-slate-200
                                        @endif">{{ $item->status->label() }}</span>
                                    </div>

                                    <div class="mt-4">
                                        <div class="flex items-center justify-between text-xs text-slate-500">
                                            <span>پیشرفت مرحله</span>
                                            <span class="font-extrabold text-slate-700">
                                                {{ $item->progress_percent }}%
                                            </span>
                                        </div>
                                        <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
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
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2">
                                    <path d="M21 12a9 9 0 1 1-2.64-6.36"></path>
                                    <path d="M21 3v6h-6"></path>
                                </svg>
                            </span>

                            <div>
                                <div class="text-base font-extrabold">آپدیت‌ها و تغییرات</div>
                                <div class="mt-1 text-xs text-slate-500">تازه‌ترین اتفاقات پروژه</div>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach($project->changelogs as $item)
                                <div
                                    class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 hover:bg-slate-50 transition">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-sm font-extrabold">{{ $item->title }}</div>
                                            <div class="mt-2 text-sm text-slate-700 leading-7">
                                                {!! nl2br(htmlspecialchars(e($item->body))) !!}
                                            </div>
                                        </div>
                                        @if($item->published_at)
                                            <span
                                                class="text-xs text-slate-500 shrink-0">{{ time_ago($item->published_at)  }}</span>
                                        @endif
                                    </div>

                                    @if(count($item->tagsList()) > 0)
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($item->tagsList() as $tag)
                                                <span
                                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">
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
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 20h9"></path>
              <path d="M12 4h9"></path>
              <path d="M4 9h16"></path>
              <path d="M4 15h16"></path>
            </svg>
          </span>
                        <div>
                            <div class="text-base font-extrabold">جزئیات سریع</div>
                            <div class="mt-1 text-xs text-slate-500">اطلاعات مهم پروژه</div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">وضعیت</span>
                            <span class="font-extrabold"
                                  :class="project.status==='done' ? 'text-emerald-700' : 'text-brand-700'"
                            >{{ \App\Domain\Project\Enums\ProjectStatusEnum::from($project->status->value)->label() }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">پیشرفت</span>
                            <span class="font-extrabold">{{ $project->progress_percent . '%' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">نسخه فعلی</span>
                            <span class="font-extrabold">{{ $project->current_version }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">سازنده</span>
                            <span class="font-extrabold">{{ $project->creator_name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">پلتفرم</span>
                            <span class="font-extrabold">{{ $project->platform }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">آخـرین آپدیت</span>
                            <span class="font-extrabold">{{ jalali_date($project->last_updated_at, 'd F Y') }}</span>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <a href="#updates"
                           class="rounded-2xl bg-brand-500 px-4 py-3 text-sm font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition text-center">
                            مشاهده آپدیت‌ها
                        </a>
                        <a href="#roadmap"
                           class="rounded-2xl bg-white border border-slate-200 text-brand-700 px-4 py-3 text-sm font-extrabold hover:bg-brand-50 hover:border-brand-200 transition text-center">
                            نقشه راه
                        </a>
                    </div>
                </div>

                <!-- Placeholder -->
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 shadow-sm">
                    <div class="font-extrabold text-slate-700">بنر / تبلیغات</div>
                    <div class="mt-2 text-sm text-slate-500">جایگاه پیشنهادی برای بنر مرتبط با بازی</div>
                    <div class="mt-3 text-xs text-slate-500">size: 320×250</div>
                </div>

            </aside>

        </section>

    </main>
@endsection
