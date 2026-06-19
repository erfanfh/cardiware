@if($product->tags)
    <x-front.cart>
        <div class="mb-4 text-sm font-extrabold text-slate-900">برچسب‌ها</div>

        <div class="flex flex-wrap gap-2">
            @foreach($product->tags as $tag)
                <a
                    href="#"
                    class="rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-[11px] font-extrabold text-slate-700 transition hover:border-brand-100 hover:bg-brand-50 hover:text-brand-800"
                >
                    {{ $tag->title }}
                </a>
            @endforeach
        </div>
    </x-front.cart>
@endif
