@props([
    'action' => null,
    'bindAction' => null,
    'method' => 'post', // post | put | patch | delete | get
])

@php
    $method = strtolower($method);

    $formMethod = $method === 'get' ? 'get' : 'post';

    $baseAttributes = $attributes->class('confirmationForm');

    if (filled($bindAction)) {
        $baseAttributes = $baseAttributes->merge([
            ':action' => $bindAction,
        ]);
    } elseif (filled($action)) {
        $baseAttributes = $baseAttributes->merge([
            'action' => $action,
        ]);
    }

    if ($method === 'delete') {
        $baseAttributes = $baseAttributes
            ->class('js-confirm')
            ->merge([
                'data-confirm-title' => 'تأیید حذف',
                'data-confirm-text' => 'آیا از حذف این مورد مطمئن هستید؟ این عملیات قابل بازگشت نیست.',
            ]);
    }
@endphp

<form
    method="{{ $formMethod }}"
    {{ $baseAttributes }}
    x-data
    @keydown.enter="
        const tag = $event.target.tagName.toLowerCase();
        const type = ($event.target.type || '').toLowerCase();

        if (tag !== 'textarea' && type !== 'submit' && type !== 'button') {
            $event.preventDefault();
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
