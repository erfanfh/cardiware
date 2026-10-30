<footer class="border-t border-edge/60 bg-surface/40">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-3">
            <div>
                <a href="#hero" class="flex items-center gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-accent to-accent-2 text-lg font-black text-page">M</span>
                    <span class="leading-tight">
                        <span class="block text-base font-extrabold text-ink">{{ $data['product_name'] }}</span>
                        <span class="block font-num text-[10px] font-bold tracking-[0.35em] text-muted">{{ $data['brand'] }}</span>
                    </span>
                </a>
                <p class="mt-5 max-w-xs text-sm leading-7 text-muted">{{ $data['footer']['about'] }}</p>
            </div>

            <div>
                <h4 class="text-sm font-extrabold text-ink">لینک‌ها</h4>
                <ul class="mt-4 space-y-2.5">
                    @foreach($data['footer']['links'] as $link)
                        <li><a href="{{ $link['href'] }}" class="text-sm text-muted transition-colors hover:text-accent">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-extrabold text-ink">جامعه Moddingway</h4>
                <div class="mt-4 flex flex-wrap gap-2.5">
                    @foreach($data['footer']['socials'] as $social)
                        <a href="{{ $social['href'] }}" class="rounded-xl border border-edge/70 bg-surface px-4 py-2 text-xs font-bold text-muted transition-all hover:border-accent/40 hover:text-accent">{{ $social['label'] }}</a>
                    @endforeach
                </div>
                <a href="{{ $data['buy_url'] }}" class="btn-primary mt-6 !px-5 !py-2.5 text-sm">خرید پچ</a>
            </div>
        </div>

        <p class="mt-12 border-t border-edge/50 pt-6 text-xs leading-6 text-muted/70">{{ $data['footer']['disclaimer'] }}</p>

        <div class="mt-4 flex flex-col items-center justify-between gap-2 text-xs text-muted sm:flex-row">
            <span class="font-num">{{ $data['footer']['copyright'] }}</span>
            <span class="font-num tracking-widest">MODDINGWAY.IR</span>
        </div>
    </div>
</footer>
