@php
    use App\Application\Seo\Support\SeoTemplateVariableRegistry;
    use App\Domain\Content\Enums\PostStatusEnum;

    $isEdit = isset($post) && $post;
    $quickUrl = $isEdit ? route('posts.detail.quick', $post->id) : null;
    $itemUrl = $isEdit
        ? ($post->isPubliclyVisible() ? $post->url : route('preview.posts', $post->id))
        : null;
    $itemUrlLabel = $isEdit && $post->isPubliclyVisible() ? 'مشاهده' : 'پیش‌نمایش';

    $statuses = [];
    foreach (PostStatusEnum::cases() as $status){
        $statuses[$status->value] = $status->label();
    }

    $postSeoVariables = app(SeoTemplateVariableRegistry::class)->forContext('post');
@endphp

<x-admin.page columns="2">
    <x-slot:sidebar>
        @if($isEdit && filled($quickUrl))
            <x-admin.metabox title="لینک کوتاه">
                <div
                    x-data="{
                    copied: false,
                    timer: null,

                    async copyLink() {
                        const input = this.$refs.shortLink;
                        const value = input.value;

                        try {
                            await navigator.clipboard.writeText(value);
                        } catch (error) {
                            input.focus();
                            input.select();
                            document.execCommand('copy');
                            window.getSelection()?.removeAllRanges();
                        }

                        this.copied = true;

                        clearTimeout(this.timer);

                        this.timer = setTimeout(() => {
                            this.copied = false;
                        }, 1800);
                    }
                }"
                    class="space-y-3"
                >
                    <div
                        class="flex overflow-hidden rounded-xl border border-slate-200 bg-slate-50
                           transition focus-within:border-brand-400 focus-within:ring-4
                           focus-within:ring-brand-500/10"
                    >
                        <input
                            x-ref="shortLink"
                            type="text"
                            readonly
                            dir="ltr"
                            value="{{ $quickUrl }}"
                            class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                               text-left text-sm text-slate-700 outline-none ring-0
                               focus:border-0 focus:ring-0"
                            aria-label="لینک کوتاه نوشته"
                        >

                        <button
                            type="button"
                            x-on:click="copyLink"
                            class="flex shrink-0 items-center gap-1.5 border-r border-slate-200
                               bg-white px-3 text-xs font-bold text-slate-600 transition cursor-pointer
                               hover:bg-brand-50 hover:text-brand-700"
                        >
                            <svg
                                x-show="!copied"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-4 w-4"
                            >
                                <rect x="8" y="8" width="11" height="11" rx="2"></rect>
                                <path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path>
                            </svg>

                            <svg
                                x-show="copied"
                                x-cloak
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <path d="m5 12 4 4L19 6"></path>
                            </svg>

                            <span x-text="copied ? 'کپی شد' : 'کپی'"></span>
                        </button>
                    </div>
                </div>
            </x-admin.metabox>
        @endif

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
                    label="سنجاق کردن نوشته"
                >
                    <x-admin.form.select
                        name="is_pinned"
                        :options="['0' => 'خیر', '1' => 'بله']"
                        :value="old('is_pinned', $post?->is_pinned)"
                    />
                </x-admin.field>

                @if($isEdit)
                    <x-admin.field
                        class="col-span-12"
                        label="افزایش نمایشی بازدید"
                    >
                        <x-admin.form.input
                            name="display_views_offset"
                            type="number"
                            min="0"
                            max="4294967295"
                            step="1"
                            :value="old('display_views_offset', $post->display_views_offset ?? 0)"
                        />
                    </x-admin.field>
                @endif

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
                                         :value="old('front_badge', $selectedFeatures?->frontBadge?->value ?? '')"/>
                </x-admin.field>

                <x-admin.field
                    class="col-span-12 flex items-center gap-3 [&_label]:mb-0"
                    label="نوشته ویژه"
                >
                    <x-admin.form.switch name="is_special"
                                         :checked="old('is_special', $selectedFeatures?->isSpecial ?? false)"/>
                </x-admin.field>

                <x-admin.field
                    class="col-span-12 flex items-center gap-3 [&_label]:mb-0"
                    label="نوشته VIP"
                >
                    <x-admin.form.switch name="is_vip" :checked="old('is_vip', $selectedFeatures?->isVip ?? false)"/>
                </x-admin.field>

                <x-admin.field
                    class="col-span-12 flex items-center gap-3 [&_label]:mb-0"
                    label="نوشته به روزرسانی شده"
                >
                    <x-admin.form.switch name="is_updated"
                                         :checked="old('is_updated', $selectedFeatures?->isUpdated ?? false)"/>
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

        <x-admin.metabox title="برچسب‌ها" class="!overflow-visible">
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
                :search-url="route('admin.posts.tags.ajax-search')"
            />
        </x-admin.metabox>

        <x-admin.metabox title="دسته‌بندی‌ها">
            <x-admin.form.categories
                name="category_ids"
                :items="$allCategories"
                :selected="old('category_ids', $selectedCategoryIds ?? [])"
                :quick-create-url="route('admin.posts.categories.quick-create')"
                :search-url="route('admin.posts.categories.ajax-search')"
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
        :itemUrl="$itemUrl"
        :itemUrlLabel="$itemUrlLabel"
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
        title="سئو"
        storageKey="seo-section"
    >
        @include('admin.content.posts.partials.seo-box')
    </x-admin.metabox>

    <x-admin.metabox
        title="باکس دانلود"
        storageKey="post-download-sections"
    >
        @include('admin.content.posts.partials.download-sections')
    </x-admin.metabox>

    <x-admin.metabox
        title="گالری تصاویر"
        storageKey="post-image-gallery"
    >
        @include('admin.content.posts.partials.image-gallery')
    </x-admin.metabox>

    <x-admin.metabox
        title="گالری ویدیویی"
        storageKey="post-video-gallery"
    >
        @include('admin.content.posts.partials.video-gallery')
    </x-admin.metabox>

    @foreach(\App\Domain\Content\Enums\PostInformationBoxTypeEnum::cases() as $postInformationBox)
        @php
            $informationBoxes = $post?->informationBoxes?->keyBy(fn ($box) => $box->type->value) ?? collect();
        @endphp

        <x-admin.metabox
            title="باکس های اطلاعات - {{ $postInformationBox->label() }}"
            storageKey="post-information-box-{{ $postInformationBox->value }}"
        >
            <div class="flex flex-col gap-5">
                @if($postInformationBox !== \App\Domain\Content\Enums\PostInformationBoxTypeEnum::PREREQUISITES)
                    <x-admin.field label="عنوان">
                        <x-admin.form.input
                            name="information_boxes[{{ $postInformationBox->value }}][title]"
                            :value="old('information_boxes.' . $postInformationBox->value . '.title', $informationBoxes->get($postInformationBox->value)?->title)"
                        />
                    </x-admin.field>
                @endif

                <x-admin.field>
                    <x-admin.form.editor
                        name="information_boxes[{{ $postInformationBox->value }}][body]"
                        :value="old('information_boxes.' . $postInformationBox->value . '.body', $informationBoxes->get($postInformationBox->value)?->body)"
                        preset="full"
                        height="500"
                        :toolbar-sticky="false"
                        placeholder="متن ..."
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>
    @endforeach
</x-admin.page>
