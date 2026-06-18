@php
    use App\Application\Seo\Support\SeoTemplateVariableRegistry;
    use App\Domain\Catalog\Enums\ProductStatusEnum;
    use App\Domain\Marketplace\Enums\StoreStatusEnum;use App\Domain\Marketplace\Models\Store;

    $isEdit = isset($store) && $store;

    $statuses = [];
    foreach (StoreStatusEnum::cases() as $status){
        $statuses[$status->value] = $status->label();
    }

    $seoVariables = app(SeoTemplateVariableRegistry::class)->forContext('store');
@endphp

<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="انتشار">
            <div class="grid grid-cols-12 gap-5">
                <x-admin.field
                    class="col-span-12"
                    label="وضعیت"
                >
                    <x-admin.form.select
                        name="status"
                        :options="$statuses"
                        :value="old('status', $store?->status?->value)"
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="تصویر شاخص">
            <x-admin.form.media-picker
                mode="field"
                name="picture"
                :value="[
                    'id' => old('picture.id', $store?->picture_id),
                    'path' => old('picture.path', $store?->picture?->url),
                ]"
                layout="vertical"
            />
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form.wp-title-slug
        titleName="title"
        slugName="slug"
        :titleValue="$store->title ?? ''"
        :slugValue="$store->slug ?? ''"
        basePath="{{ route('home') }}/stores/"
        :scope="\App\Domain\Slug\Enums\SlugScopeEnum::STORE"
        :currentId="$store->id ?? null"
        :itemUrl="$store ? route('stores.detail', $store) : null"
    />

    <x-admin.metabox title="توضیحات">
        <x-admin.field>
            <x-admin.form.editor
                name="body"
                :value="old('body', $store?->body)"
                preset="full"
                height="500"
                placeholder="درباره فروشنده ..."
            />
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox title="زمینه فعالیت">
        <x-admin.field>
            <x-admin.form.input
                name="activity_field"
                :value="old('activity_field', $store?->activity_field)"
            />
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox
        title="سئو"
        storageKey="seo-section"
    >
        @include('admin.marketplace.stores.partials.seo-box')
    </x-admin.metabox>
</x-admin.page>
