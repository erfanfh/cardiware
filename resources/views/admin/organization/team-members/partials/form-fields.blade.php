@php
    $teamMember = $teamMember ?? null;

    $selectedPictureId = old(
        'picture_id',
        $teamMember?->picture_id
    );

    $selectedPicture = $selectedPicture ?? [];
@endphp

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.metabox title="اطلاعات اصلی عضو تیم">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin.field required label="نام">
                <x-admin.form.input
                    name="first_name"
                    autocomplete="given-name"
                    :value="old('first_name', $teamMember?->first_name)"
                />
            </x-admin.field>

            <x-admin.field required label="نام خانوادگی">
                <x-admin.form.input
                    name="last_name"
                    autocomplete="family-name"
                    :value="old('last_name', $teamMember?->last_name)"
                />
            </x-admin.field>

            <div class="md:col-span-2">
                <x-admin.field label="عنوان شغلی">
                    <x-admin.form.input
                        name="job_title"
                        :value="old('job_title', $teamMember?->job_title)"
                    />
                </x-admin.field>
            </div>

            <div class="md:col-span-2">
                <x-admin.field label="معرفی کوتاه">
                    <x-admin.form.textarea
                        name="bio"
                        rows="6"
                        :value="old('bio', $teamMember?->bio)"
                    />
                </x-admin.field>
            </div>
        </div>
    </x-admin.metabox>

    <x-admin.metabox
        title="تصویر عضو تیم"
        class="overflow-visible"
    >
        <x-admin.form.media-picker
            mode="field"
            name="picture"
            :value="[
                    'id' => old('picture.id', $teamMember?->picture_id),
                    'path' => old('picture.path', $teamMember?->picture?->url),
                ]"
            layout="vertical"
        />
    </x-admin.metabox>

    <x-admin.metabox title="اطلاعات تماس">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin.field label="ایمیل">
                <x-admin.form.input
                    type="email"
                    dir="ltr"
                    name="email"
                    autocomplete="email"
                    :value="old('email', $teamMember?->email)"
                />
            </x-admin.field>

            <x-admin.field label="شماره موبایل">
                <x-admin.form.input
                    type="tel"
                    dir="ltr"
                    name="mobile"
                    autocomplete="tel"
                    :value="old('mobile', $teamMember?->mobile)"
                />
            </x-admin.field>

            <div class="md:col-span-2">
                <x-admin.field
                    label="آدرس وب‌سایت"
                    hint="آدرس را به صورت کامل و همراه با https وارد کنید."
                >
                    <x-admin.form.input
                        type="url"
                        dir="ltr"
                        name="website_url"
                        placeholder="https://example.com"
                        :value="old('website_url', $teamMember?->website_url)"
                    />
                </x-admin.field>
            </div>
        </div>
    </x-admin.metabox>
</x-admin.page>
