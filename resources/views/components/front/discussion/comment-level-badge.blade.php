@props(['level', 'userId' => null])

{{-- سطح باشگاه نویسنده‌ی دیدگاه؛ لینک به جدول برترین‌ها --}}
<a
    href="{{ $userId ? route('loyalty.members.show', $userId) : route('loyalty.leaderboard') }}"
    title="سطح {{ $level->title }} در باشگاه مخاطبان"
    aria-label="سطح {{ $level->title }} در باشگاه مخاطبان"
    class="inline-flex h-5 shrink-0 items-center gap-1 rounded-lg border px-1.5 text-[10px] font-black leading-none transition hover:brightness-95 {{ $level->colorClasses()['chip'] }}"
>
    @if(filled($level->badge))
        <span aria-hidden="true">{{ $level->badge }}</span>
    @endif
    <span>{{ $level->title }}</span>
</a>
