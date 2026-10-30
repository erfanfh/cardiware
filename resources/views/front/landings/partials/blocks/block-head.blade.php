{{-- سربرگ اختیاری بلوک — $block['title'] دوزبانه --}}
@if(!empty($block['title']))
    <div class="mx-auto mb-8 max-w-2xl text-center">
        <h3 class="text-2xl font-black text-ink">{{ $block['title'] }}</h3>
        @if(!empty($block['subtitle']))
            <p class="mt-3 text-sm leading-7 text-muted">{{ $block['subtitle'] }}</p>
        @endif
    </div>
@endif
