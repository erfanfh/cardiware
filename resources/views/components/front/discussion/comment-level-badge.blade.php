@props(['level', 'userId' => null])

{{-- Club level of comment author; link to leaderboard --}}
<a
    href="{{ $userId ? route('loyalty.members.show', $userId) : route('loyalty.leaderboard') }}"
    title="Level {{ $level->title }} in the Community Club"
    aria-label="Level {{ $level->title }} in the Community Club"
    class="inline-flex h-5 shrink-0 items-center gap-1 rounded-lg border px-1.5 text-[10px] font-black leading-none transition hover:brightness-95 {{ $level->colorClasses()['chip'] }}"
>
    @if(filled($level->badge))
        <span aria-hidden="true">{{ $level->badge }}</span>
    @endif
    <span>{{ $level->title }}</span>
</a>
