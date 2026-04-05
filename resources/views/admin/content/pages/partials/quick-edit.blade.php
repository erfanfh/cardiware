<x-admin.list.quick-edit-row :row-id="$page->id" colspan="20">
    <div class="space-y-5">
        <x-admin.form
            method="PATCH"
            action="{{ route('admin.pages.quick-update', $page) }}"
            class="space-y-5"
            x-on:submit.prevent="submitQuickEdit($event.target, {{ $page->id }})"
        >
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-800">
                            ویرایش سریع برگه
                        </h3>

                        <x-admin.badge color="blue">
                            شناسه: {{ $page->id }}
                        </x-admin.badge>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.form.button
                        type="submit"
                        variant="solid"
                        size="sm"
                        ::disabled="isSaving({{ $page->id }})"
                    >
                        <span x-show="!isSaving({{ $page->id }})">ذخیره تغییرات</span>
                        <span x-show="isSaving({{ $page->id }})" x-cloak>در حال ذخیره...</span>
                    </x-admin.form.button>

                    <x-admin.form.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="close()"
                        ::disabled="isSaving({{ $page->id }})"
                    >
                        انصراف
                    </x-admin.form.button>
                </div>
            </div>

            <x-admin.form.ajax-error-box
                :row-id="$page->id"
                :fields="['title', 'slug', 'status']"
            />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <x-admin.field label="عنوان برگه">
                    <x-admin.form.input
                        name="title"
                        :value="old('title', $page->title)"
                    />
                </x-admin.field>

                <x-admin.field label="اسلاگ">
                    <x-admin.form.input
                        name="slug"
                        class="direction-ltr"
                        :value="old('slug', $page->slug)"
                    />
                </x-admin.field>

                <x-admin.field label="وضعیت">
                    <x-admin.form.select
                        name="status"
                        :options="$statuses"
                        :value="old('status', $page->status?->value)"
                    />
                </x-admin.field>
            </div>
        </x-admin.form>
    </div>
</x-admin.list.quick-edit-row>
