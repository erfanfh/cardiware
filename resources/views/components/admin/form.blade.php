@props([
    'action',
    'method' => 'post' // post | put | get
])

@php
    $method = strtolower($method);
@endphp

<form method="{{ $method === 'get' ? 'get' : 'post' }}"
      {{ $attributes }}
      action="{{ $action }}"
      x-data
      @keydown.enter.prevent="
        if (
            $event.target.tagName.toLowerCase() !== 'textarea' &&
            $event.target.type !== 'submit' &&
            $event.target.type !== 'button'
        ) {
            $event.preventDefault()
        }
    ">
    @if($method !== 'get')
        @csrf
    @endif

    @if($method === 'put')
        @method('PUT')
    @endif

    {{ $slot }}
</form>
