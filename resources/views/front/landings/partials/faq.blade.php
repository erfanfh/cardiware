<section id="faq" class="section-pad scroll-mt-24" x-data="{ open: 0 }">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

        @include('front.landings.partials.section-head', [
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'],
        ])

        <div class="mt-12 space-y-3">
            @foreach($section['items'] as $i => $item)
                <div class="reveal overflow-hidden rounded-2xl border border-edge/70 bg-surface/60 transition-colors duration-300"
                     :class="open === {{ $i }} ? 'border-accent/40 !bg-surface' : ''">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start"
                            :aria-expanded="open === {{ $i }}">
                        <span class="text-base font-bold text-ink">{{ $item['q'] }}</span>
                        <span class="grid size-8 shrink-0 place-items-center rounded-full border transition-all duration-300"
                              :class="open === {{ $i }} ? 'rotate-180 border-accent/40 text-accent' : 'border-edge text-muted'">
                            @include('front.landings.partials.icon', ['name' => 'chevron-down', 'class' => 'size-4'])
                        </span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse x-cloak>
                        <p class="border-t border-edge/50 px-6 pb-6 pt-4 text-sm leading-8 text-muted">{{ $item['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
