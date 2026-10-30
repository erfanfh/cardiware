<section class="relative border-y border-edge/60 bg-surface/40">
    <div class="mx-auto grid max-w-7xl grid-cols-2 divide-edge/40 px-4 sm:grid-cols-3 sm:divide-x sm:rtl:divide-x-reverse lg:grid-cols-5">
        @foreach($section['items'] as $item)
            <div class="reveal py-10 text-center">
                <div class="font-num text-3xl font-black text-ink sm:text-4xl">
                    <span data-count="{{ $item['value'] }}">۰</span><span class="text-accent">{{ $item['suffix'] }}</span>
                </div>
                <div class="mt-2 text-xs font-medium text-muted sm:text-sm">{{ $item['label'] }}</div>
            </div>
        @endforeach
    </div>
</section>
