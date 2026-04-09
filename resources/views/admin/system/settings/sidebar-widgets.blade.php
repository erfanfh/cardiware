@extends('admin.layout')

@section('title', 'تنظیمات ابزارک های سایدبار')

@section('content')
    <x-admin.form action="{{ route('admin.settings.sidebar-widgets.store') }}" method="put">
        <x-admin.page>
            <x-admin.tabs
                :tabs="[
                    'tools' => 'ابزارهای کاربردی',
                    'categories' => 'دسته بندی ها',
                    'populars' => 'مطالب پربازدید',
                    'projects' => 'پروژه ها',
                    'comments' => 'آخرین دیدگاه ها',
                ]"
                active="tools"
            >
                <x-admin.tabs.panel name="tools">
                    <div class="space-y-6">
                        <x-admin.metabox title="ابزارهای کاربردی" :collapsible="false">
                            <div class="space-y-4">
                                <div class="text-xs text-slate-500">
                                    آیتم‌های ابزارهای کاربردی را از این بخش مدیریت کنید.
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-admin.field label="عنوان">
                                        <x-admin.form.input name="tools_title"
                                                            :value="get_setting('sidebarWidgets', 'tools_title')"/>
                                    </x-admin.field>

                                    <x-admin.field label="زیرعنوان">
                                        <x-admin.form.input name="tools_subtitle"
                                                            :value="get_setting('sidebarWidgets', 'tools_subtitle')"/>
                                    </x-admin.field>
                                </div>
                                <x-admin.form.repeater
                                    name="tools_items"
                                    :items="old('tools_items', get_setting('sidebarWidgets', 'tools_items') ?? [])"
                                    title="آیتم ها"
                                    addText="افزودن آیتم"
                                    emptyText="هنوز آیتمی اضافه نشده است."
                                    headingKey="title"
                                    headingPlaceholder="آیتم بدون عنوان"
                                    :defaultItem="[
                                        'title' => '',
                                        'subtitle' => '',
                                        'url' => '',
                                        'image_id' => null,
                                        'image_path' => null,
                                        'sort_order' => 0,
                                    ]"
                                >
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-4">
                                            <x-admin.field
                                                label="عنوان"
                                            >
                                                <x-admin.form.input
                                                    x-bind:name="field(i, 'title')"
                                                    x-model="it.title"
                                                />
                                            </x-admin.field>

                                            <x-admin.field
                                                label="زیر عنوان"
                                            >
                                                <x-admin.form.input
                                                    x-bind:name="field(i, 'subtitle')"
                                                    x-model="it.subtitle"
                                                />
                                            </x-admin.field>

                                            <x-admin.field
                                                label="لینک"
                                            >
                                                <x-admin.form.input
                                                    x-bind:name="field(i, 'url')"
                                                    x-model="it.url"
                                                    dir="ltr"
                                                />
                                            </x-admin.field>
                                        </div>

                                        <x-admin.field label="تصویر"
                                        >
                                            <x-admin.form.media-picker
                                                mode="repeater"
                                                name="image"
                                            />
                                        </x-admin.field>
                                    </div>
                                </x-admin.form.repeater>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="categories">
                    <div class="space-y-6">
                        <x-admin.metabox title="دسته بندی ها" :collapsible="false" class="overflow-visible">
                            <div class="space-y-4">
                                <div class="text-xs text-slate-500">
                                    آیتم‌های دسته بندی ها را از این بخش مدیریت کنید.
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-admin.field label="عنوان">
                                        <x-admin.form.input name="categories_title"
                                                            :value="get_setting('sidebarWidgets', 'categories_title')"/>
                                    </x-admin.field>

                                    <x-admin.field label="زیرعنوان">
                                        <x-admin.form.input name="categories_subtitle"
                                                            :value="get_setting('sidebarWidgets', 'categories_subtitle')"/>
                                    </x-admin.field>
                                </div>

                                @php
                                    $categoryItems = collect(get_setting('sidebarWidgets', 'categories_items') ?? []);

                                    $categoryTitles = \App\Domain\Taxonomy\Models\TaxonomyTerm::categories()
                                        ->whereIn('id', $categoryItems->pluck('category_id')->filter()->all())
                                        ->pluck('title', 'id');

                                    $categoryItems = $categoryItems
                                        ->map(fn ($item) => [
                                            'category_id' => $item['category_id'] ?? '',
                                            'category_title' => isset($item['category_id']) ? $categoryTitles[$item['category_id']] : null,
                                            'sort_order' => $item['sort_order'] ?? 0,
                                        ])
                                        ->all();
                                @endphp

                                <x-admin.form.repeater
                                    name="categories_items"
                                    :items="old('categories_items', $categoryItems)"
                                    title="دسته بندی ها"
                                    addText="افزودن دسته بندی"
                                    emptyText="هنوز دسته بندی ای اضافه نشده است."
                                    headingKey="category_title"
                                    :defaultItem="[
                                        'category_id' => '',
                                        'category_title' => '',
                                        'sort_order' => 0,
                                    ]"
                                >
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        <x-admin.field>
                                            <x-admin.form.async-select
                                                x-bind:name="field(i, 'category_id')"
                                                x-bind:data-initial-option="it.category_id ? JSON.stringify({
                                                    value: String(it.category_id),
                                                    label: it.category_title || ('#' + it.category_id)
                                                }) : ''"
                                                x-on:mw-async-select-change="
                                                    it.category_id = $event.detail.value ?? '';
                                                    it.category_title = $event.detail.label ?? '';
                                                "
                                                label="دسته اصلی"
                                                :multiple="false"
                                                :search-url="route('admin.categories.ajax-search')"
                                                placeholder="دسته اصلی را انتخاب کنید..."
                                            />
                                        </x-admin.field>
                                    </div>
                                </x-admin.form.repeater>
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="populars">
                    <div class="space-y-6">
                        <x-admin.metabox title="مطالب پربازدید" :collapsible="false">
                            <div class="space-y-4">
                                <div class="text-xs text-slate-500">
                                    اطلاعات مطالب پربازدید را از این بخش مدیریت کنید.
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <x-admin.field label="عنوان">
                                        <x-admin.form.input name="populars_title"
                                                            :value="get_setting('sidebarWidgets', 'populars_title')"/>
                                    </x-admin.field>

                                    <x-admin.field label="زیرعنوان">
                                        <x-admin.form.input name="populars_subtitle"
                                                            :value="get_setting('sidebarWidgets', 'populars_subtitle')"/>
                                    </x-admin.field>

                                    <x-admin.field label="تعداد نوشته نمایشی">
                                        <x-admin.form.input name="populars_posts_count" type="number"
                                                            :value="get_setting('sidebarWidgets', 'populars_posts_count')"/>
                                    </x-admin.field>
                                </div>

                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="projects">
                    <div class="space-y-6">
                        <x-admin.metabox title="پروژه ها" :collapsible="false" class="overflow-visible">
                            <div class="space-y-4">
                                <div class="text-xs text-slate-500">
                                    آیتم‌های پروژه ها را از این بخش مدیریت کنید.
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-admin.field label="عنوان">
                                        <x-admin.form.input name="projects_title"
                                                            :value="get_setting('sidebarWidgets', 'projects_title')"/>
                                    </x-admin.field>

                                    <x-admin.field label="زیرعنوان">
                                        <x-admin.form.input name="projects_subtitle"
                                                            :value="get_setting('sidebarWidgets', 'projects_subtitle')"/>
                                    </x-admin.field>
                                </div>

                                @php
                                    $projectItems = collect(get_setting('sidebarWidgets', 'projects_items') ?? []);

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
                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="comments">
                    <div class="space-y-6">
                        <x-admin.metabox title="آخرین دیدگاه ها" :collapsible="false">
                            <div class="space-y-4">
                                <div class="text-xs text-slate-500">
                                    اطلاعات آخرین دیدگاه ها را از این بخش مدیریت کنید.
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <x-admin.field label="عنوان">
                                        <x-admin.form.input name="comments_title"
                                                            :value="get_setting('sidebarWidgets', 'comments_title')"/>
                                    </x-admin.field>

                                    <x-admin.field label="زیرعنوان">
                                        <x-admin.form.input name="comments_subtitle"
                                                            :value="get_setting('sidebarWidgets', 'comments_subtitle')"/>
                                    </x-admin.field>

                                    <x-admin.field label="تعداد دیدگاه نمایشی">
                                        <x-admin.form.input name="comments_count" type="number"
                                                            :value="get_setting('sidebarWidgets', 'comments_count')"/>
                                    </x-admin.field>
                                </div>

                            </div>
                        </x-admin.metabox>
                    </div>
                </x-admin.tabs.panel>
            </x-admin.tabs>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
