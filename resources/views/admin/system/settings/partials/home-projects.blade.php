<div class="space-y-6">
    <x-admin.metabox title="پروژه ها" :collapsible="false" class="overflow-visible">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4 mb-4">
            <x-admin.field label="عنوان باکس">
                <x-admin.form.input
                    name="projects_title"
                    :value="old('projects_title', get_setting('home', 'projects_title'))"
                />
            </x-admin.field>
        </div>

        <x-admin.field>
            @php
                $projectItems = collect(get_setting('home', 'projects_items') ?? []);

                $projectTitles = \App\Domain\Project\Models\Project::whereIn('id', $projectItems->pluck('project_id')->filter()->all())
                    ->pluck('title', 'id');

                $projectItems = $projectItems
                    ->map(fn ($item) => [
                        'project_id' => $item['project_id'] ?? '',
                        'project_title' => isset($item['project_id']) ? $projectTitles[$item['project_id']] : null,
                        'sort_order' => $item['sort_order'] ?? 0,
                    ])
                    ->all();
            @endphp

            <x-admin.form.repeater
                name="projects_items"
                :items="old('projects_items', $projectItems)"
                title="پروژه ها"
                addText="افزودن پروژه"
                emptyText="هنوز پروژه ای اضافه نشده است."
                headingKey="project_title"
                :defaultItem="[
                            'project_id' => '',
                            'project_title' => '',
                            'sort_order' => 0,
                        ]"
            >
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-admin.field>
                        <x-admin.form.async-select
                            x-bind:name="field(i, 'project_id')"
                            x-bind:data-initial-option="it.project_id ? JSON.stringify({
                                                    value: String(it.project_id),
                                                    label: it.project_title || ('#' + it.project_id)
                                                }) : ''"
                            x-on:mw-async-select-change="
                                                    it.project_id = $event.detail.value ?? '';
                                                    it.project_title = $event.detail.label ?? '';
                                                "
                            label="پروژه"
                            :multiple="false"
                            :search-url="route('admin.projects.ajax-search')"
                            placeholder="پروژه را انتخاب کنید..."
                        />
                    </x-admin.field>
                </div>
            </x-admin.form.repeater>
        </x-admin.field>
    </x-admin.metabox>
</div>
