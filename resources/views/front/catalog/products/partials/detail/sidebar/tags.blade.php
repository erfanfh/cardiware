@if($product->tags)
    <x-front.cart>
        <div class="text-sm font-extrabold text-slate-900 mb-4">برچسب‌ها</div>
        <div class="flex flex-wrap gap-2">
            @foreach($product->tags as $tag)
                <a href="#"
                   class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-3 py-2 rounded-xl hover:bg-brand-50 hover:border-brand-100 hover:text-brand-800 transition">
                    {{ $tag->title }}
                </a>
            @endforeach
        </div>
    </x-front.cart>
@endif
