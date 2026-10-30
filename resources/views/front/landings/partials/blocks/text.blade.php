{{--
    بلوک متن — {type: text, title?, subtitle?, text}
--}}
<div class="reveal">
    @include('front.landings.partials.blocks.block-head', ['block' => $block])
    @if(!empty($block['title']))
        <p class="mx-auto max-w-3xl text-center text-base leading-9 text-muted">{{ $block['text'] }}</p>
    @else
        <p class="mx-auto max-w-3xl text-center text-base leading-9 text-muted">{{ $block['text'] }}</p>
    @endif
</div>
