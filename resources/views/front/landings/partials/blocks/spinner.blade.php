{{--
    بلوک اسپینر ۳۶۰° — {type: spinner, title?, dir, frames, caption?}
--}}
<div class="reveal">
    @include('front.landings.partials.blocks.block-head', ['block' => $block])
    @include('front.landings.partials.spinner', ['spin' => $block])
</div>
