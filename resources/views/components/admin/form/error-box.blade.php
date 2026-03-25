@props([
    'errors' => null,
])

@php
    $errorBag = $errors ?? session('errors');
    $hasErrors = $errorBag && $errorBag->any();
@endphp

@if($hasErrors)
    <div {{ $attributes->merge([
        'class' => 'rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm'
    ]) }}>
        <div class="mb-2 flex items-center gap-2 font-bold text-rose-800">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path
                    d="M12 8v5m0 3h.01M10.29 3.86l-7.5 13A1 1 0 0 0 3.65 18h16.7a1 1 0 0 0 .86-1.5l-7.5-13a1 1 0 0 0-1.72 0Z"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>لطفاً خطاهای زیر را بررسی کن</span>
        </div>

        <ul class="space-y-1 pr-5 list-disc marker:text-rose-500">
            @foreach($errorBag->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
