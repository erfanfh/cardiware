<section class="section-pad bg-surface/30">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

        @include('front.landings.partials.section-head', [
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'],
        ])

        @php
            // Allowed change icons: usable with token [fire], [heart] etc. at the start of each line
            $changelogIcons = [
                'heart', 'fire', 'sparkles', 'star', 'rocket', 'zap',
                'bug', 'shield-check', 'gift', 'refresh', 'thumb-up', 'surprise', 'flame2',
            ];

            // Each note Can be a string with leading token or an array {icon, text} be
            $parseNote = function (mixed $note) use ($changelogIcons): array {
                if (is_array($note)) {
                    $icon = in_array($note['icon'] ?? '', $changelogIcons, true) ? $note['icon'] : null;

                    return [$icon, (string) ($note['text'] ?? '')];
                }

                $text = (string) $note;

                if (preg_match('/^\[([a-z0-9-]+)\]\s*/i', $text, $m) && in_array(strtolower($m[1]), $changelogIcons, true)) {
                    return [strtolower($m[1]), mb_substr($text, mb_strlen($m[0]))];
                }

                return [null, $text];
            };
        @endphp

        <div class="relative mt-14 pr-6">
            {{-- Timeline line --}}
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
                                @php [$noteIcon, $noteText] = $parseNote($note); @endphp

                                <li class="flex items-start gap-2.5 text-sm leading-7 text-muted">
                                    @if($noteIcon)
                                        <span class="mt-1 grid size-5 shrink-0 place-items-center rounded-md border border-accent/25 bg-accent/10 text-accent [&_svg]:size-3.5" aria-hidden="true">
                                            @include('icons.changelog.' . $noteIcon)
                                        </span>
                                    @else
                                        <span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-accent/60" aria-hidden="true"></span>
                                    @endif

                                    {{ $noteText }}
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
