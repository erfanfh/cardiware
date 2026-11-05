@if($bTitle !== '' || $bSubtitle !== '')
    <div class="mx-auto max-w-3xl text-center">
        @if($bSubtitle !== '')<div class="text-xs font-black lp-accent">{{ $bSubtitle }}</div>@endif
        @if($bTitle !== '')<h2 class="mt-2 text-2xl font-black sm:text-3xl">{{ $bTitle }}</h2>@endif
        @if($bBody !== '' && !in_array($type, ['text'], true))<p class="mt-3 whitespace-pre-line text-sm leading-7 {{ $sectionMuted }}">{{ $bBody }}</p>@endif
    </div>
@endif
