@props([
    'mode' => 'field',
    'name' => null,
    'itemKey' => null,
    'mediaId' => null,
])

@livewire(
    'admin.media.media-picker',
    [
        'mode' => $mode,
        'name' => $name,
        'itemKey' => $itemKey,
        'selectedMediaFileId' => $mediaId,
    ],
    key('media-picker-'.($itemKey ?? $name ?? uniqid()))
)
