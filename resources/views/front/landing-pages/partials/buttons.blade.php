@if($buttons)
    <div class="mt-6 flex flex-wrap gap-3 {{ !empty($center) ? 'justify-center' : '' }}">
        @foreach($buttons as $button)
            <a href="{{ $button['link'] }}" class="{{ $button['class'] }} inline-flex h-12 items-center rounded-2xl px-6 text-sm font-black transition">{{ $button['text'] }}</a>
        @endforeach
    </div>
@endif
