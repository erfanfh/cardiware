@if($items->isNotEmpty())
    <nav class="md:mt-4">
        <div class="flex items-center justify-between gap-3">
            <div class="hidden md:flex items-center text-sm font-extrabold text-slate-800 dark:text-slate-200">
                @foreach($items as $item)
                    @if(!$loop->first)
                        <span class="mx-1 text-slate-300 select-none dark:text-slate-700">|</span>
                    @endif

                    @if($item['is_mega_menu'] && $item['children']->isNotEmpty())
                        @include('front.header.navigation.mega-item', ['item' => $item])
                    @elseif($item['children']->isNotEmpty())
                        @include('front.header.navigation.dropdown-item', ['item' => $item])
                    @else
                        @include('front.header.navigation.link-item', ['item' => $item])
                    @endif
                @endforeach
            </div>

            <div class="hidden md:flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"></div>
        </div>
    </nav>
@endif
