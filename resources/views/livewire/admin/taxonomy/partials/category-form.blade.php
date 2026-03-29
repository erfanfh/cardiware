<div
    wire:key="taxonomy-sidebar-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
    x-data
    x-on:taxonomy-edit-started.window="
        if (window.innerWidth < 1280) {
            $el.scrollIntoView({ behavior: 'smooth', block: 'start' })
        }

        $nextTick(() => {
            setTimeout(() => $refs.taxonomyTitleInput?.focus(), 120)
        })
    "
>
    <x-admin.metabox :collapsible="false" title="اطلاعات {{ $taxonomyLabel }}">
        <div class="space-y-5">
            <form
                wire:submit.prevent="save"
                wire:key="taxonomy-form-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
                wire:loading.class="opacity-70"
                wire:target="save,cancelEditing"
                class="space-y-5 transition"
            >
                <x-admin.form.simple-validation-errors/>

                <div class="space-y-4">
                    <x-admin.field
                        label="عنوان"
                        for="taxonomy-term-title"
                    >
                        <x-admin.form.input
                            id="taxonomy-term-title"
                            x-ref="taxonomyTitleInput"
                            wire:model.defer="title"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="عبارت یکتا"
                        for="taxonomy-term-slug"
                        hint="اختیاری است. اگر خالی بماند، به‌صورت خودکار ساخته می‌شود."
                    >
                        <x-admin.form.input
                            id="taxonomy-term-slug"
                            class="direction-ltr"
                            wire:model.defer="slug"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="دسته‌بندی والد"
                        for="taxonomy-term-parent"
                        hint="اختیاری است. اگر انتخاب نشود، این دسته‌بندی در سطح ریشه قرار می‌گیرد."
                    >
                        <x-admin.form.select
                            id="taxonomy-term-parent"
                            wire:model.defer="parentId"
                            :options="$parentOptions"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="توضیحات"
                        for="taxonomy-term-description"
                    >
                        <x-admin.form.textarea
                            id="taxonomy-term-description"
                            rows="3"
                            wire:model.defer="description"
                        />
                    </x-admin.field>
                </div>

                <div
                    wire:key="taxonomy-form-actions-{{ $this->isEditing ? 'edit' : 'create' }}"
                    class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4"
                >
                    <x-admin.form.button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                    >
                        <svg
                            wire:loading
                            wire:target="save"
                            class="ml-2 h-4 w-4 animate-spin"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25"
                                    stroke-width="4"></circle>
                            <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                                  stroke-linecap="round"></path>
                        </svg>

                        <span>
                            {{ $this->isEditing ? 'ذخیره تغییرات' : "افزودن {$taxonomyLabel}" }}
                        </span>
                    </x-admin.form.button>

                    @if($this->isEditing)
                        <x-admin.form.button
                            type="button"
                            wire:click="cancelEditing"
                            variant="secondary"
                        >
                            انصراف
                        </x-admin.form.button>
                    @endif
                </div>
            </form>
        </div>
    </x-admin.metabox>
</div>
