@php
    use App\Domain\Content\Enums\PostStatusEnum;$isEdit = isset($post) && $post;
    $statuses = [];
    foreach (PostStatusEnum::cases() as $status){
        $statuses[$status->value] = $status->label();
    }
@endphp

<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="انتشار">
            <div class="grid grid-cols-12 gap-5">
                <x-admin.field
                    class="col-span-12"
                    label="تاریخ ایجاد"
                >
                    <x-admin.form.date-picker
                        name="published_at"
                        :value="old('published_at', jalali_date($post?->published_at))"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="وضعیت"
                >
                    <x-admin.form.select
                        name="status"
                        :options="$statuses"
                        :value="old('status', $post?->status?->value)"
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="ویژگی‌ها">
            <div class="grid grid-cols-12 gap-5">
                <x-admin.field
                    class="col-span-12 flex items-center gap-3 [&_label]:mb-0"
                    label="لیبل"
                >
                    @php
                        $frontBadges = collect(\App\Domain\Feature\Enums\FrontBadgeOptionEnum::cases())
                        ->mapWithKeys(fn($status) => [
                            $status->value => $status->label(),
                        ])
                        ->all();
                    @endphp

                    <x-admin.form.select name="front_badge" :options="$frontBadges" placeholder="--- انتخاب کنید ---"
                                         style="width: auto !important;"
                                         :value="old('front_badge', $selectedFeatures?->frontBadge?->value)"/>
                </x-admin.field>

                <x-admin.field
                    class="col-span-12 flex items-center gap-3 [&_label]:mb-0"
                    label="نوشته ویژه"
                >
                    <x-admin.form.switch name="is_special"
                                         :checked="old('is_special', $selectedFeatures?->isSpecial)"/>
                </x-admin.field>

                <x-admin.field
                    class="col-span-12 flex items-center gap-3 [&_label]:mb-0"
                    label="نوشته VIP"
                >
                    <x-admin.form.switch name="is_vip" :checked="old('is_vip', $selectedFeatures?->isVip)"/>
                </x-admin.field>

                <x-admin.field
                    class="col-span-12 flex items-center gap-3 [&_label]:mb-0"
                    label="نوشته به روزرسانی شده"
                >
                    <x-admin.form.switch name="is_updated"
                                         :checked="old('is_updated', $selectedFeatures?->isUpdated)"/>
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="تصویر شاخص">
            <x-admin.form.media-picker
                mode="field"
                name="picture"
                :value="[
                    'id' => old('picture.id', $post?->picture_id),
                    'path' => old('picture.path', $post?->picture?->url),
                ]"
                layout="vertical"
            />
        </x-admin.metabox>

        <x-admin.metabox title="برچسب‌ها">
            @php
                $oldExistingIds = collect(old('tags.existing', []))
                    ->filter()
                    ->map(fn ($id) => (int) $id);

                $oldExistingTags = \App\Domain\Taxonomy\Models\TaxonomyTerm::query()
                    ->whereIn('id', $oldExistingIds)
                    ->get(['id', 'title'])
                    ->map(fn ($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->title,
                    ]);

                $oldNewTags = collect(old('tags.new', []))
                    ->filter()
                    ->map(fn ($name) => [
                        'id' => null,
                        'name' => trim((string) $name),
                    ]);

                $resolvedSelectedTags = old('tags.existing') !== null || old('tags.new') !== null
                    ? $oldExistingTags->concat($oldNewTags)->values()->all()
                    : ($selectedTags ?? []);
            @endphp

            <x-admin.form.tags
                existingName="tags[existing]"
                newName="tags[new]"
                :selected="$resolvedSelectedTags"
            />
        </x-admin.metabox>

        <x-admin.metabox title="دسته‌بندی‌ها">
            <x-admin.form.categories
                name="category_ids"
                :items="$allCategories"
                :selected="old('category_ids', $selectedCategoryIds ?? [])"
                quick-create-url="{{ route('admin.categories.quick-create') }}"
            />
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form.wp-title-slug
        titleName="title"
        slugName="slug"
        :titleValue="$post->title ?? ''"
        :slugValue="$post->slug ?? ''"
        basePath="{{ route('home') }}/"
        :scope="\App\Domain\Slug\Enums\SlugScopeEnum::CONTENT"
        :currentId="$post->id ?? null"
        :itemUrl="$post?->url"
    />

    <x-admin.metabox title="توضیحات">
        <x-admin.field required>
            <x-admin.form.editor
                name="body"
                :value="old('body', $post?->body)"
                preset="full"
                height="500"
                placeholder="متن نوشته ..."
            />
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox title="خلاصه">
        <x-admin.field required
                       hint="این فیلد اختیاری است. درصورتی که تکمیل نشود، خلاصه توضیحات نوشته در نظر گرفته خواهد شد.">
            <x-admin.form.textarea
                name="excerpt"
                rows="4"
                placeholder="یک خلاصه کوتاه از نوشته ..."
                value="{{ old('excerpt', $post->excerpt ?? '') }}"/>
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox
        title="باکس دانلود"
        storageKey="post-download-sections"
    >
        @include('admin.content.posts.partials.download-sections')
    </x-admin.metabox>
</x-admin.page>
