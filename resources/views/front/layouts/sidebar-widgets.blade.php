
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-3">
        <span
            class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
          @include('icons.dashboard-square-03')
        </span>
        <h2 class="text-base font-extrabold">{{ get_setting('sidebarWidgets', 'tools_title') }}</h2>
    </div>

    <p class="mt-2 text-xs text-slate-500 leading-6">
        {{ get_setting('sidebarWidgets', 'tools_subtitle') }}
    </p>

    <div class="mt-4 grid grid-cols-2 gap-3">
        @foreach(get_setting('sidebarWidgets', 'tools_items') as $item)
            <a href="{{ $item['url'] }}"
               class="rounded-2xl border border-slate-200 bg-white p-3 hover:bg-slate-50 hover:shadow-sm transition">
                <div class="flex items-center gap-3">
                    <div
                        class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center">
                        <span class="text-slate-500 text-xs font-extrabold">
                            {{--img--}}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold truncate">{{ $item['title'] }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ $item['subtitle'] }}</div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-3">
            <span
                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                @include('icons.right-to-left-list-bullet')
            </span>
        <h2 class="text-base font-extrabold">{{ get_setting('sidebarWidgets', 'categories_title') }}</h2>
    </div>

    <p class="mt-2 text-xs text-slate-500 leading-6">
        {{ get_setting('sidebarWidgets', 'categories_subtitle') }}
    </p>

    <ul class="mt-4 space-y-3">
        @foreach(get_setting('sidebarWidgets', 'categories_items') ?? [] as $categoryItem)
            @php
                $category = \App\Domain\Taxonomy\Models\TaxonomyTerm::categories()->findOrFail($categoryItem['category_id']);
            @endphp
            <li>
                <a href="{{ route('categories', $category) }}"
                   class="flex items-center justify-between rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                    <span class="font-semibold text-slate-800">{{ $category->title }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
              <span
                  class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                  @include('icons.view')
              </span>
            <h2 class="text-base font-extrabold">{{ get_setting('sidebarWidgets', 'populars_title') }}</h2>
        </div>

        <p class="mt-2 text-xs text-slate-500 leading-6">
            {{ get_setting('sidebarWidgets', 'populars_subtitle') }}
        </p>
    </div>

    <div class="mt-4 space-y-3">
        @foreach(\App\Domain\Content\Models\Post::take(get_setting('sidebarWidgets', 'populars_posts_count'))->get() as $post)
            <a href="{{ route('contents.detail', $post) }}"
               class="flex gap-3 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                <img src="{{ $post->picture?->url }}" alt="{{ $post->title }}"
                     class="h-14 w-14 rounded-2xl object-cover"/>
                <div class="min-w-0">
                    <div class="text-sm font-extrabold leading-6 truncate">
                        {{ $post->title }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        {{ $post->excerpt }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-3">
            <span
                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
              @include('icons.folder-library')
            </span>
        <div>
            <h2 class="text-base font-extrabold">{{ get_setting('sidebarWidgets', 'projects_title') }}</h2>
            <p class="text-xs text-slate-500 mt-1">{{ get_setting('sidebarWidgets', 'projects_subtitle') }}</p>
        </div>
    </div>

    <div class="mt-4 space-y-3">
        @foreach(get_setting('sidebarWidgets', 'projects_items') ?? [] as $projectItem)
            @php
                $project = \App\Domain\Project\Models\Project::findOrFail($projectItem['project_id']);
            @endphp
            <div class="rounded-2xl border border-slate-200 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold truncate">
                            <a href="{{ route('projects.detail', $project) }}">
                                {{ $project->title }}
                            </a>
                        </div>
                        <div class="mt-1 text-xs text-slate-500 truncate">{{ $project->excerpt }}</div>
                    </div>
                    <span
                        class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
                        {{ $project->progress_percent . '%' }}
                    </span>
                </div>
                <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-brand-500" style="width: {{ $project->progress_percent . '%' }}"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-3">
            <span
                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
              @include('icons.comment-01')
            </span>
        <h2 class="text-base font-extrabold">{{ get_setting('sidebarWidgets', 'comments_title') }}</h2>
    </div>

    <p class="mt-2 text-xs text-slate-500 leading-6">
        {{ get_setting('sidebarWidgets', 'comments_subtitle') }}
    </p>

    <div class="mt-4 space-y-3">
        <a href="#" class="block rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-extrabold truncate">کاربر: Amir</div>
                <div class="text-xs text-slate-500">۲ ساعت پیش</div>
            </div>
            <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2">
                «روی نسخه استیم هم جواب میده؟ چون مسیر نصب من فرق داره و ارور می‌گیرم…»
            </div>
            <div
                class="mt-2 text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl inline-flex">
                روی: چک‌لیست نصب پچ
            </div>
        </a>
    </div>
</div>
