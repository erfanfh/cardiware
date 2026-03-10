@props([
    'name' => null,
    'mediaId' => null,
])

@livewire('admin.media.media-picker', ['name' => $name, 'selectedMediaFileId' => $mediaId])
