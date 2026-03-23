@php
    use App\Domain\Project\Enums\ProjectStatusEnum;
    $isEdit = isset($project) && $project;
    $statuses = [];
    foreach (ProjectStatusEnum::cases() as $status){
        $statuses[$status->value] = $status->label();
    }
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

            <div class="pt-3 mt-3 border-t border-slate-200 flex items-center gap-2">
                <x-admin.button type="submit" variant="solid" class="flex-1">
                    {{ $isEdit ? 'ذخیره' : 'انتشار' }}
                </x-admin.button>
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

        <x-admin.metabox title="اطلاعات پروژه">
            <div class="grid grid-cols-12 gap-5">
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
                    label="تاریخ انتشار"
                >
                    <x-admin.form.date-picker
                        name="released_at"
                        :value="old('released_at', jalali_date($project?->released_at))"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="تاریخ آخرین بروزرسانی"
                >
                    <x-admin.form.date-picker
                        name="last_updated_at"
                        :value="old('last_updated_at', jalali_date($project?->last_updated_at))"
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="برچسب‌ها">
            <x-admin.form.tags
                existingName="tags[existing]"
                newName="tags[new]"
                :selected="$selectedTags ?? []"
                :suggestions="$tagSuggestions ?? []"
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
        :checkUrl="route('admin.projects.check-slug')"
        :currentId="$project->id ?? null"
        :itemUrl="$project ? route('projects.detail', $project) : null"
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

    <x-admin.metabox title="ویژگی‌ها">
        <x-admin.project.feature-repeater
            :items="old('features', $project->features ?? [])"
        />
    </x-admin.metabox>

    <x-admin.metabox title="نقشه راه">
        <x-admin.project.roadmap-item-repeater
            :items="old('roadmap_items', $project->roadmapItems ?? [])"
        />
    </x-admin.metabox>

    <x-admin.metabox title="آپدیت‌ها و تغییرات">
        <x-admin.project.changelog-repeater
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
