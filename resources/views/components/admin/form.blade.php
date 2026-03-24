@props([
    'action',
    'method' => 'post' // post | put
])

<form method="post"
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
    @csrf

    @if($method === 'put')
        @method('PUT')
    @endif

    {{ $slot }}
</form>
