@props([
    'action',
    'method' => 'post' // post | put
])

<form method="post" action="{{ $action }}" class="js-confirm">
    @csrf

    @if($method === 'put')
        @method('PUT')
    @endif

    {{ $slot }}
</form>
