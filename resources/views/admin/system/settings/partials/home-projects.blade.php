<div class="space-y-6">
    <x-admin.metabox title="پروژه ها" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="عنوان باکس">
                <x-admin.form.input
                    name="projects_title"
                    :value="old('projects_title', get_setting('home', 'projects_title'))"
                />
            </x-admin.field>

            <x-admin.field label="زیر عنوان باکس">
                <x-admin.form.input
                    name="projects_title"
                    :value="old('projects_title', get_setting('home', 'projects_subtitle'))"
                />
            </x-admin.field>
        </div>

        <x-admin.field>
            <x-admin.form.repeater
                :items="get_setting('home', 'projects_items')"
                name="projects_items"
                title="آیتم ها"
                addText="افزودن آیتم"
                :defaultItem="[
                    'project_id' => '',
                    'sort_order' => 0,
                ]"
            >
                <div class="grid grid-cols-2 gap-4">
                    <x-admin.field label="پروژه">
                        <x-admin.form.async-select
                            x-bind:name="`blocks[${index}][project_id]`"
                            :multiple="false"
                            :selected-options="[]"
                            :search-url="route('admin.projects.ajax-search')"
                            placeholder="پروژه را انتخاب کنید..."
                        />
                    </x-admin.field>
                </div>
            </x-admin.form.repeater>
        </x-admin.field>
    </x-admin.metabox>
</div>
