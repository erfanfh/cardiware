@props([
    'action',
    'method' => 'post' // post | put
])

<form method="post" action="{{ $action }}" class="mb-12">
    @csrf

    @if($method === 'put')
        @method('PUT')
    @endif

    {{ $slot }}
</form>
