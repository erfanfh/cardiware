@props([
    'action',
    'method' => 'post' // post | put
])

<form method="post" action="{{ $action }}">
    @csrf

    @if($method === 'put')
        @method('PUT')
    @endif

    {{ $slot }}
</form>
