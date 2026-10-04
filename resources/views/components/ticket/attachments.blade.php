@props([
    'message',
    'tone' => 'light',
    'darkMode' => true,
    'openInNewTab' => false,
])

@if($message->attachments->isNotEmpty())
    @php
        $isDarkTone = $tone === 'dark';
    @endphp

    <div class="mt-2.5 flex max-w-[19rem] flex-wrap gap-1.5">
        @foreach($message->attachments as $attachment)
            @php
                $thumbnailUrl = route('support.ticket-attachments.show', [
                    'ticketMessageAttachment' => $attachment,
                    'variant' => 'thumbnail',
                ]);
                $originalUrl = route('support.ticket-attachments.show', [
                    'ticketMessageAttachment' => $attachment,
                    'variant' => 'original',
                ]);
                $imageTitle = $attachment->original_name ?: 'تصویر پیوست تیکت';
            @endphp

            @if($openInNewTab)
                <a href="{{ $originalUrl }}" target="_blank" rel="noopener noreferrer" aria-label="باز کردن تصویر {{ $loop->iteration }} در تب جدید"
            @else
                <button type="button" x-on:click="$dispatch('ticket-image-open', { url: @js($originalUrl), title: @js($imageTitle) })" aria-label="نمایش تصویر {{ $loop->iteration }}"
            @endif
                @class([
                    'group relative h-[4.5rem] w-[4.5rem] shrink-0 cursor-zoom-in overflow-hidden rounded-xl border bg-slate-100 text-right transition focus:outline-none focus:ring-4 sm:h-20 sm:w-20',
                    'border-white/20 focus:ring-white/20' => $isDarkTone,
                    'border-slate-200 focus:ring-brand-100' => !$isDarkTone,
                    'dark:border-slate-600 dark:focus:ring-brand-500/20' => !$isDarkTone && $darkMode,
                ])
            >
                <img
                    src="{{ $thumbnailUrl }}"
                    alt="{{ $imageTitle }}"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                />

                <span class="absolute inset-0 bg-slate-950/0 transition group-hover:bg-slate-950/15"></span>

                <span class="absolute bottom-1.5 left-1.5 grid h-6 w-6 place-items-center rounded-lg bg-slate-950/55 text-white opacity-0 backdrop-blur transition group-hover:opacity-100">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m15 15 5 5m-2-8a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
            @if($openInNewTab)</a>@else</button>@endif
        @endforeach
    </div>
@endif
