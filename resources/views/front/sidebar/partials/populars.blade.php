@php
    $popularPostsWidget = app(\App\Application\System\Actions\Sidebar\GetSidebarPopularPostsAction::class)->execute();
@endphp

<x-front.sidebar.sidebar-item
    :title="$popularPostsWidget['title']"
    :subtitle="$popularPostsWidget['subtitle']"
    iconName="view"
>
    <div x-data="{ period: 'week' }">
        <div role="tablist" aria-label="Most viewed posts period"
             class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1 dark:bg-slate-800/80">
            @foreach(['day' => 'Today', 'week' => 'This week', 'month' => 'This month'] as $periodKey => $periodLabel)
                <button type="button" role="tab"
                        id="popular-posts-tab-{{ $periodKey }}"
                        aria-controls="popular-posts-panel-{{ $periodKey }}"
                        :aria-selected="period === '{{ $periodKey }}'"
                        @click="period = '{{ $periodKey }}'"
                        :class="period === '{{ $periodKey }}'
                            ? 'bg-white text-brand-700 shadow-sm dark:bg-slate-700 dark:text-brand-300'
                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                        class="cursor-pointer rounded-lg px-2 py-2 text-xs font-extrabold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500">
                    {{ $periodLabel }}
                </button>
            @endforeach
        </div>

        @foreach(['day', 'week', 'month'] as $periodKey)
            <div role="tabpanel"
                 id="popular-posts-panel-{{ $periodKey }}"
                 aria-labelledby="popular-posts-tab-{{ $periodKey }}"
                 x-show="period === '{{ $periodKey }}'"
                 @if($periodKey !== 'week') x-cloak @endif
                 class="mt-3">
                @forelse($popularPostsWidget['periods'][$periodKey] as $post)
                    @php($pictureUrl = $post->picture?->url)
                    <a href="{{ front_route('contents.detail', $post) }}"
                       class="group flex items-center gap-2.5 border-b border-slate-100 px-1 py-2.5 transition last:border-b-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-black tabular-nums {{ $loop->iteration <= 3 ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $loop->iteration }}
                        </span>
                        @if($pictureUrl)
                            <img src="{{ $pictureUrl }}" alt="" loading="lazy"
                                 class="h-10 w-10 shrink-0 rounded-lg object-cover"/>
                        @else
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800">
                                @include('icons.view')
                            </span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="line-clamp-2 text-sm font-bold leading-5 text-slate-800 transition group-hover:text-brand-700 dark:text-slate-100 dark:group-hover:text-brand-300">
                                {{ $post->title }}
                            </span>
                            <span class="mt-1 block text-[11px] text-slate-500 dark:text-slate-400">
                                {{ number_format((int) $post->total_views) }} views
                            </span>
                        </span>
                    </a>
                @empty
                    <p class="px-1 py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                        No post views recorded during this period yet.
                    </p>
                @endforelse
            </div>
        @endforeach
    </div>
</x-front.sidebar.sidebar-item>
