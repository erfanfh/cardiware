<section class="section-pad bg-surface/30">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

        @include('front.landings.partials.section-head', [
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'],
        ])

        <div class="relative mt-14 pr-6">
            {{-- خط تایم‌لاین --}}
            <div class="absolute inset-y-2 right-2 w-px bg-gradient-to-b from-accent/60 via-edge to-transparent" aria-hidden="true"></div>

            <div class="space-y-10">
                @foreach($section['items'] as $i => $item)
                    <article class="reveal relative">
                        <span class="absolute -right-[1.4rem] top-1.5 size-3 rounded-full border-2 border-accent bg-page {{ $i === 0 ? 'shadow-[0_0_16px_2px_var(--landing-accent)]' : '' }}" aria-hidden="true"></span>

                        <div class="flex flex-wrap items-center gap-3">
                            <span class="font-num rounded-lg border border-accent/30 bg-accent/10 px-3 py-1 text-sm font-black text-accent">{{ $item['version'] }}</span>
                            <span class="font-num text-xs font-medium text-muted">{{ $item['date'] }}</span>
                        </div>

                        <ul class="mt-3 space-y-1.5">
                            @foreach($item['notes'] as $note)
                                <li class="flex items-start gap-2.5 text-sm leading-7 text-muted">
                                    <span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-accent/60" aria-hidden="true"></span>
                                    {{ $note }}
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
