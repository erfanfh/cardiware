@props([
    'action' => null,
    'bindAction' => null,
    'method' => 'post', // post | put | patch | delete | get
])

@php
    $method = strtolower($method);

    $formMethod = $method === 'get' ? 'get' : 'post';

    $baseAttributes = $attributes->class([]);

    if (filled($bindAction)) {
        $baseAttributes = $baseAttributes->merge([
            ':action' => $bindAction,
        ]);
    } elseif (filled($action)) {
        $baseAttributes = $baseAttributes->merge([
            'action' => $action,
        ]);
    }
@endphp

<form
    class="confirmationForm"
    method="{{ $formMethod }}"
    {{ $baseAttributes }}
    x-data
    @keydown.enter.prevent="
        if (
            $event.target.tagName.toLowerCase() !== 'textarea' &&
            $event.target.type !== 'submit' &&
            $event.target.type !== 'button'
        ) {
            $event.preventDefault()
        }
    "
>
    @if($method !== 'get')
        @csrf
    @endif

    @if($method === 'put')
        @method('PUT')
    @elseif($method === 'patch')
        @method('PATCH')
    @elseif($method === 'delete')
        @method('DELETE')
    @endif

    {{ $slot }}
</form>
