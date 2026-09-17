@php
    use App\Domain\Content\Enums\PageStatusEnum;
    $isEdit = isset($page) && $page;
    $itemUrl = $isEdit
        ? ($page->isPubliclyVisible() ? $page->url : route('preview.pages', $page->id))
        : null;
    $itemUrlLabel = $isEdit && $page->isPubliclyVisible() ? 'مشاهده' : 'پیش‌نمایش';
    $statuses = [];
    foreach (PageStatusEnum::cases() as $status){
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
                        :value="old('published_at', jalali_date($page?->published_at))"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="وضعیت"
                >
                    <x-admin.form.select
                        name="status"
                        :options="$statuses"
                        :value="old('status', $page?->status?->value)"
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="تنظیمات">
            <div class="grid grid-cols-12 gap-5">
                <x-admin.field
                    class="col-span-12"
                    label="قالب صفحه"
                >
                    @inject('templates', \App\Application\Content\Services\PageTemplateService::class)

                    <x-admin.form.select name="template">
                        <option value="">پیش‌فرض</option>

                        @foreach($templates->all() as $key => $label)
                            <option value="{{ $key }}" @selected(old('template', $page?->template) === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-admin.form.select>
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="تصویر شاخص">
            <x-admin.form.media-picker
                mode="field"
                name="picture"
                :value="[
                    'id' => old('picture.id', $page?->picture_id),
                    'path' => old('picture.path', $page?->picture?->url),
                ]"
                layout="vertical"
            />
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form.wp-title-slug
        titleName="title"
        slugName="slug"
        :titleValue="$page->title ?? ''"
        :slugValue="$page->slug ?? ''"
        basePath="{{ route('home') }}/"
        :scope="\App\Domain\Slug\Enums\SlugScopeEnum::CONTENT"
        :currentId="$page->id ?? null"
        :itemUrl="$itemUrl"
        :itemUrlLabel="$itemUrlLabel"
    />

    <x-admin.metabox title="توضیحات">
        <x-admin.field required>
            <x-admin.form.editor
                name="body"
                :value="old('body', $page?->body)"
                preset="full"
                height="500"
                placeholder="متن نوشته ..."
            />
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox title="خلاصه">
        <x-admin.field required
                       hint="این فیلد اختیاری است. درصورتی که تکمیل نشود، خلاصه توضیحات برگه در نظر گرفته خواهد شد.">
            <x-admin.form.textarea
                name="excerpt"
                rows="4"
                placeholder="یک خلاصه کوتاه از برگه ..."
                value="{{ old('excerpt', $page->excerpt ?? '') }}"/>
        </x-admin.field>
    </x-admin.metabox>
</x-admin.page>
