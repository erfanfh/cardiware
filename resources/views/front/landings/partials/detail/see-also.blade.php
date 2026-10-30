@php($locale = $locale ?? 'fa')
@php($prefix = $locale === 'en' ? '/en' : '')
<section class="border-t border-edge/60 bg-surface/30 section-pad !py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-lg font-black text-ink">{{ $locale === 'en' ? 'See also' : 'ببینید همچنین' }}</h2>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($data['pages'] as $pageKey => $page)
                @if($pageKey !== $pageSlug)
                    <a href="{{ $prefix }}/{{ $slug }}/{{ $pageKey }}"
                       class="group rounded-2xl border border-edge/70 bg-surface/60 p-5 transition-all duration-300 hover:-translate-y-0.5 hover:border-accent/40">
                        <span class="grid size-11 place-items-center rounded-xl bg-accent/10 text-accent">
                            @include('front.landings.partials.icon', [
                                'name' => ['leagues' => 'globe', 'stadiums' => 'stadium', 'trophies' => 'trophy', 'faces' => 'face'][$pageKey] ?? 'bolt',
                            ])
                        </span>
                        <h3 class="mt-4 text-sm font-extrabold text-ink group-hover:text-accent">{{ $page['label'] }}</h3>
                        <span class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-accent">
                            {{ $locale === 'en' ? 'Open' : 'مشاهده' }}
                            @include('front.landings.partials.icon', ['name' => 'arrow', 'class' => 'size-3 transition-transform group-hover:-translate-x-1 rtl:group-hover:-translate-x-1 ltr:group-hover:translate-x-1'])
                        </span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</section>
