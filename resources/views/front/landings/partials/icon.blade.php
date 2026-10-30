{{-- آیکون خطی داخلی — $name : نام آیکون، $class : کلاس اضافه --}}
@switch($name ?? '')
    @case('check')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        @break
    @case('chevron-down')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        @break
    @case('play')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72c0 .8.87 1.3 1.56.9l11-6.86a1.05 1.05 0 0 0 0-1.8l-11-6.86c-.69-.4-1.56.1-1.56.9Z"/></svg>
        @break
    @case('download')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        @break
    @case('shield')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1Z"/></svg>
        @break
    @case('bolt')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg>
        @break
    @case('face')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
        @break
    @case('stadium')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="12" rx="10" ry="6.5"/><ellipse cx="12" cy="12" rx="6" ry="3.6"/><ellipse cx="12" cy="12" rx="2" ry="1.2"/></svg>
        @break
    @case('mic')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>
        @break
    @case('gamepad')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><path d="M15 13h.01"/><path d="M18 11h.01"/><rect x="2" y="6" width="20" height="12" rx="6"/></svg>
        @break
    @case('tv')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17 2 12 7 7 2"/></svg>
        @break
    @case('trophy')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
        @break
    @case('shirt')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23Z"/></svg>
        @break
    @case('globe')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
        @break
    @case('menu')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
        @break
    @case('x')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        @break
    @case('arrow')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        @break
    @case('crown')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8Z"/><path d="M6 19h12"/></svg>
        @break
    @case('star')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26 6.85.72-5.1 4.6 1.44 6.7L12 16.8l-6.09 3.48 1.44-6.7-5.1-4.6 6.85-.72L12 2Z"/></svg>
        @break
    @case('fire')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c.8 3.2-1.6 4.8-2.9 6.7C7.8 10.6 7 12.2 7 14a5 5 0 0 0 10 0c0-1.5-.6-2.7-1.4-3.7-.4.9-1 1.5-2 1.9.5-2.7-.8-6.6-1.6-10.2Z"/></svg>
        @break
    @case('target')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/></svg>
        @break
    @case('medal')
        <svg {!! $attributes ?? '' !!} class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 2l2.5 6M17 2l-2.5 6"/><circle cx="12" cy="14" r="6"/><path d="m12 11.5 1 2 2.2.3-1.6 1.5.4 2.2-2-1-2 1 .4-2.2-1.6-1.5 2.2-.3 1-2Z" fill="currentColor" stroke="none"/></svg>
        @break
@endswitch
