{{--
    بلوک کیت سه‌بعدی — {type: kit3d, title?, texture, accent, secondary, caption?}
--}}
<div class="reveal">
    @include('front.landings.partials.blocks.block-head', ['block' => $block])
    @include('front.landings.partials.kit3d', ['kit3d' => $block])
</div>
