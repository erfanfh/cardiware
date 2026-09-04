@php
    use App\Application\Seo\Support\SeoTemplateVariableRegistry;
    use App\Domain\Project\Enums\ProjectStatusEnum;

    $isEdit = isset($project) && $project;

    $statuses = [];
    foreach (ProjectStatusEnum::cases() as $status){
        $statuses[$status->value] = $status->label();
    }

    $projectSeoVariables = app(SeoTemplateVariableRegistry::class)->forContext('project');
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
                        :value="old('status', $project?->status?->value)"
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="کاور پروژه">
            <x-admin.form.media-picker
                mode="field"
                name="cover"
                :value="[
                    'id' => old('cover.id', $project?->cover_id),
                    'path' => old('cover.path', $project?->cover?->url),
                ]"
                layout="vertical"
            />
        </x-admin.metabox>

        <x-admin.metabox title="باکس مشاهده محصول">
            <div class="grid grid-cols-12 gap-5">
                <x-admin.field
                    class="col-span-12"
                    label="لینک محصول"
                >
                    <div class="flex items-center gap-3">
                        <x-admin.form.input class="direction-ltr" name="product_url"
                                            value="{{ old('product_url', $project?->product_url) }}"/>
                    </div>
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="عنوان باکس"
                >
                    <x-admin.form.input
                        name="product_box_title"
                        :value="old('product_box_title', $project?->product_box_title)"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="زیرعنوان باکس"
                >
                    <x-admin.form.input
                        name="product_box_subtitle"
                        :value="old('product_box_subtitle', $project?->product_box_subtitle)"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="متن دکمه باکس"
                >
                    <x-admin.form.input
                        name="product_box_button_text"
                        :value="old('product_box_button_text', $project?->product_box_button_text)"
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="اطلاعات پروژه">
            <div class="grid grid-cols-12 gap-5">
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
                            :value="old('display_views_offset', $project->display_views_offset ?? 0)"
                        />
                    </x-admin.field>
                @endif

                <x-admin.field
                    class="col-span-12"
                    label="درصد پیشرفت (0 تا 100)"
                >
                    <div class="flex items-center gap-3">
                        <x-admin.form.input type="number" min="0" max="100" name="progress_percent"
                                            value="{{ old('progress_percent', $project->progress_percent ?? 0) }}"/>
                    </div>
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="نسخه فعلی"
                >
                    <x-admin.form.input
                        name="current_version"
                        :value="old('current_version', $project?->current_version)"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="نام سازنده"
                >
                    <x-admin.form.input
                        name="creator_name"
                        :value="old('creator_name', $project?->creator_name)"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="پلتفرم"
                >
                    <x-admin.form.input
                        name="platform"
                        :value="old('platform', $project?->platform)"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="تاریخ انتشار پروژه"
                >
                    <x-admin.form.date-picker
                        name="released_at"
                        :value="old('released_at', jalali_date($project?->released_at))"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="تاریخ آخرین بروزرسانی پروژه"
                >
                    <x-admin.form.date-picker
                        name="last_updated_at"
                        :value="old('last_updated_at', jalali_date($project?->last_updated_at))"
                    />
                </x-admin.field>
            </div>
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
    </x-slot:sidebar>

    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form.wp-title-slug
        titleName="title"
        slugName="slug"
        :titleValue="$project->title ?? ''"
        :slugValue="$project->slug ?? ''"
        basePath="{{ route('home') }}/projects/"
        :scope="\App\Domain\Slug\Enums\SlugScopeEnum::PROJECT"
        :currentId="$project->id ?? null"
        :itemUrl="$project ? front_route('projects.detail', $project) : null"
    />

    <x-admin.metabox title="توضیحات">
        <x-admin.field required>
            <x-admin.form.editor
                name="body"
                :value="old('body', $project?->body)"
                preset="full"
                height="500"
                placeholder="توضیحات کامل پروژه ..."
            />
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox title="خلاصه">
        <x-admin.field required
                       hint="این فیلد اختیاری است. درصورتی که تکمیل نشود، خلاصه توضیحات پروژه در نظر گرفته خواهد شد.">
            <x-admin.form.textarea
                name="excerpt"
                rows="4"
                placeholder="یک خلاصه کوتاه از پروژه ..."
                value="{{ old('excerpt', $project->excerpt ?? '') }}"/>
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox
        title="سئو"
        storageKey="seo-section"
    >
        @include('admin.projects.projects.partials.seo-box')
    </x-admin.metabox>

    <x-admin.metabox title="ویژگی‌ها">
        <x-admin.modules.project.feature-repeater
            :items="old('features', $project->features ?? [])"
        />
    </x-admin.metabox>

    <x-admin.metabox title="نقشه راه">
        <x-admin.modules.project.roadmap-item-repeater
            :items="old('roadmap_items', $project->roadmapItems ?? [])"
        />
    </x-admin.metabox>

    <x-admin.metabox title="آپدیت‌ها و تغییرات">
        <x-admin.modules.project.changelog-repeater
            :items="old('changelogs', $project?->changelogs?->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'tags' => $item->tags,
                'body' => $item->body,
                'published_at' => $item->published_at ? jalali_date($item->published_at) : null,
            ]) ?? [])"
        />
    </x-admin.metabox>
</x-admin.page>
