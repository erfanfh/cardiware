<x-admin.list.quick-edit-row :row-id="$post->id" colspan="20">
    <div class="space-y-5">
        <x-admin.form
            method="PATCH"
            action="{{ route('admin.posts.quick-update', $post) }}"
            class="space-y-5"
            x-on:submit.prevent="submitQuickEdit($event.target, {{ $post->id }})"
        >
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-800">
                            ویرایش سریع نوشته
                        </h3>

                        <x-admin.badge color="blue">
                            شناسه: {{ $post->id }}
                        </x-admin.badge>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.form.button
                        type="submit"
                        variant="solid"
                        size="sm"
                        ::disabled="isSaving({{ $post->id }})"
                    >
                        <span x-show="!isSaving({{ $post->id }})">ذخیره تغییرات</span>
                        <span x-show="isSaving({{ $post->id }})" x-cloak>در حال ذخیره...</span>
                    </x-admin.form.button>

                    <x-admin.form.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="close()"
                        ::disabled="isSaving({{ $post->id }})"
                    >
                        انصراف
                    </x-admin.form.button>
                </div>
            </div>

            <x-admin.form.ajax-error-box
                :row-id="$post->id"
                :fields="['title', 'slug', 'status', 'progress_percent', 'tags']"
            />

            <div class="grid grid-cols-12 gap-5">
                <div class="grid col-span-12 lg:col-span-6 grid-cols-2 gap-5">
                    <x-admin.field label="عنوان نوشته" class="col-span-2">
                        <x-admin.form.input
                            name="title"
                            :value="old('title', $post->title)"
                        />
                    </x-admin.field>

                    <x-admin.field label="اسلاگ" class="col-span-2">
                        <x-admin.form.input
                            name="slug"
                            class="direction-ltr"
                            :value="old('slug', $post->slug)"
                        />
                    </x-admin.field>

                    <x-admin.field label="وضعیت">
                        <x-admin.form.select
                            name="status"
                            :options="$statuses"
                            :value="old('status', $post->status?->value)"
                        />
                    </x-admin.field>
                </div>

                <x-admin.field
                    label="برچسب‌ها"
                    hint="برچسب‌ها را با کاما از هم جدا کنید."
                    class="col-span-12 lg:col-span-6"
                >
                    <x-admin.form.textarea
                        name="tags"
                        rows="6"
                        :value="old('tags', $post->tags?->pluck('title')->implode('، '))"
                    />
                </x-admin.field>
            </div>
        </x-admin.form>
    </div>
</x-admin.list.quick-edit-row>
