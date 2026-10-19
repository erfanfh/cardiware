<div class="space-y-5">
    <x-admin.field label="تصویر بنر">
        <x-admin.form.media-picker
            mode="field"
            name="download_banner"
            :value="[
                'id' => old('download_banner.id', $post?->download_banner_media_id),
                'path' => old('download_banner.path', $post?->downloadBanner?->url),
            ]"
            layout="vertical"
            preview-height="h-40"
        />
        @error('download_banner_media_id')
        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </x-admin.field>

    <x-admin.field label="لینک بنر">
        <x-admin.form.input
            name="download_banner_url"
            type="url"
            dir="ltr"
            placeholder="https://example.com"
            :value="old('download_banner_url', $post?->download_banner_url)"
        />
    </x-admin.field>
</div>
