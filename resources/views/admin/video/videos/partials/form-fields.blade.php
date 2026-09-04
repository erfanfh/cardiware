@php
    use App\Domain\Video\Enums\VideoSourceTypeEnum;
    use App\Domain\Video\Enums\VideoStatusEnum;
    $video = $video ?? null;
    $selectedTags = $selectedTags ?? [];
    $oldExistingIds = collect(old('tags.existing', []))->filter()->map(fn ($id) => (int)$id);
    $oldExistingTags = \App\Domain\Taxonomy\Models\TaxonomyTerm::query()->whereIn('id', $oldExistingIds)->get(['id','title'])->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->title]);
    $oldNewTags = collect(old('tags.new', []))->filter()->map(fn ($name) => ['id' => null, 'name' => trim((string)$name)]);
    $resolvedSelectedTags = old('tags.existing') !== null || old('tags.new') !== null ? $oldExistingTags->concat($oldNewTags)->values()->all() : $selectedTags;
@endphp

<x-admin.form.validation-errors class="mb-6"/>
<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="انتشار" :collapsible="false">
            <div class="space-y-4">
                <x-admin.field label="وضعیت">
                    <x-admin.form.select name="status"
                                         :options="collect(VideoStatusEnum::cases())->mapWithKeys(fn($v)=>[$v->value=>$v->label()])->all()"
                                         :value="old('status', $video?->status?->value ?? VideoStatusEnum::INACTIVE->value)"/>
                </x-admin.field>
                <x-admin.field label="تاریخ انتشار">
                    <x-admin.form.date-picker name="published_at"
                                              :value="old('published_at', $video?->published_at ? jalali_date($video->published_at, 'Y/m/d H:i') : '')"/>
                </x-admin.field>
                @if($video)
                    <x-admin.field label="افزایش نمایشی بازدید">
                        <x-admin.form.input
                            name="display_views_offset"
                            type="number"
                            min="0"
                            max="4294967295"
                            step="1"
                            :value="old('display_views_offset', $video->display_views_offset ?? 0)"
                        />
                    </x-admin.field>
                @endif
                <div class="flex items-center justify-between"><span class="text-xs font-bold text-slate-700">ویدیوی ویژه</span>
                    <x-admin.form.switch name="is_featured"
                                         :checked="old('is_featured', $video?->is_featured ?? false)"/>
                </div>
                @if($video?->isPubliclyVisible())
                    <a href="{{ $video->url }}" target="_blank"
                       class="flex justify-center rounded-xl bg-brand-50 px-3 py-2 text-xs font-black text-brand-700">مشاهده
                        در سایت</a>
                @endif
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="کاور ویدیو">
            <x-admin.form.media-picker mode="field" name="cover"
                                       :value="['id'=>old('cover.id',$video?->cover_media_file_id),'path'=>old('cover.path',$video?->cover?->url)]"
                                       layout="vertical"/>
        </x-admin.metabox>

        <x-admin.metabox title="دیدگاه‌ها">
            <div class="space-y-4">
                <div class="flex items-center justify-between"><span class="text-xs font-bold text-slate-700">فعال‌بودن دیدگاه</span>
                    <x-admin.form.switch name="comments_enabled"
                                         :checked="old('comments_enabled',$video?->comments_enabled ?? true)"/>
                </div>
                <div class="flex items-center justify-between"><span class="text-xs font-bold text-slate-700">دیدگاه مهمان</span>
                    <x-admin.form.switch name="guest_comments_enabled"
                                         :checked="old('guest_comments_enabled',$video?->guest_comments_enabled ?? true)"/>
                </div>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="برچسب‌ها" class="!overflow-visible">
            <x-admin.form.tags existingName="tags[existing]" newName="tags[new]" :selected="$resolvedSelectedTags"
                               :search-url="route('admin.videos.tags.ajax-search')"/>
        </x-admin.metabox>
        <x-admin.metabox title="دسته‌بندی‌ها">
            <x-admin.form.categories name="category_ids" :items="$allCategories"
                                     :selected="old('category_ids',$selectedCategoryIds ?? [])"
                                     :quick-create-url="route('admin.videos.categories.quick-create')"
                                     :search-url="route('admin.videos.categories.ajax-search')"/>
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.wp-title-slug titleName="title" slugName="slug" :titleValue="$video?->title ?? ''"
                                :slugValue="$video?->slug ?? ''" :basePath="url('/videos').'/'"
                                :scope="\App\Domain\Slug\Enums\SlugScopeEnum::VIDEO" :currentId="$video?->id"
                                :itemUrl="$video?->url"/>

    <x-admin.metabox title="منبع و پخش ویدیو">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <x-admin.field label="نوع منبع">
                <x-admin.form.select name="source_type"
                                     :options="collect(VideoSourceTypeEnum::cases())->mapWithKeys(fn($v)=>[$v->value=>$v->label()])->all()"
                                     :value="old('source_type',$video?->source_type?->value ?? VideoSourceTypeEnum::APARAT->value)"/>
            </x-admin.field>
            <x-admin.field label="لینک ویدیو" class="md:col-span-2">
                <x-admin.form.input name="video_url" dir="ltr" :value="old('video_url',$video?->video_url)"/>
            </x-admin.field>
            <x-admin.field label="مدت (ثانیه)">
                <x-admin.form.input name="duration_seconds" type="number"
                                    :value="old('duration_seconds',$video?->duration_seconds)"/>
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="خلاصه">
        <x-admin.form.textarea name="excerpt" rows="4" :value="old('excerpt',$video?->excerpt)"/>
    </x-admin.metabox>
    <x-admin.metabox title="توضیحات کامل">
        <x-admin.form.editor name="body" :value="old('body',$video?->body)" preset="full" height="420"/>
    </x-admin.metabox>
    <x-admin.metabox
        title="سئو">@php($seoBoxConfig = app(\App\Application\Seo\Actions\Boxes\BuildVideoSeoBoxConfigAction::class)->execute($video))
        <x-admin.seo.box :config="$seoBoxConfig"/>
    </x-admin.metabox>
</x-admin.page>
