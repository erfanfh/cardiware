<div
    wire:key="redirect-sidebar-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
    x-data
    x-on:redirect-edit-started.window="
        if (window.innerWidth < 1280) {
            $el.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            })
        }

        $nextTick(() => {
            setTimeout(
                () => $refs.redirectSourceInput?.focus(),
                120
            )
        })
    "
>
    <x-admin.metabox
        :collapsible="false"
        title="{{ $this->isEditing ? 'ویرایش ریدایرکت' : 'ایجاد ریدایرکت' }}"
    >
        <form
            wire:submit.prevent="save"
            wire:key="redirect-form-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
            wire:loading.class="opacity-70"
            wire:target="save,cancelEditing"
            class="space-y-5 transition confirmationForm"
        >
            <x-admin.form.simple-validation-errors/>

            <div class="space-y-4">
                <x-admin.field
                    label="مسیر مبدا"
                    for="redirect-source-path"
                >
                    <x-admin.form.input
                        id="redirect-source-path"
                        x-ref="redirectSourceInput"
                        wire:model.defer="sourcePath"
                        dir="ltr"
                        class="text-left"
                        placeholder="/old-page"
                    />
                </x-admin.field>

                <x-admin.field
                    label="کد وضعیت"
                    for="redirect-status-code"
                >
                    <x-admin.form.select
                        id="redirect-status-code"
                        wire:model.live="statusCode"
                        :options="$statusCodeOptions"
                    />
                </x-admin.field>

                @if((int) $statusCode !== 410)
                    <x-admin.field
                        label="آدرس مقصد"
                        for="redirect-target-url"
                    >
                        <x-admin.form.input
                            id="redirect-target-url"
                            wire:model.defer="targetUrl"
                            dir="ltr"
                            class="text-left"
                            placeholder="/new-page یا https://example.com/new-page"
                        />
                    </x-admin.field>
                @else
                    <div
                        class="rounded-2xl border border-amber-200 bg-amber-50 px-3 py-3 text-xs font-bold leading-6 text-amber-800">
                        در ریدایرکت 410 مقصدی ذخیره نمی‌شود و درخواست با پاسخ Gone برگردانده خواهد شد.
                    </div>
                @endif

                <x-admin.field
                    label="وضعیت"
                    for="redirect-is-active"
                >
                    <x-admin.form.select
                        id="redirect-is-active"
                        wire:model.defer="isActive"
                        :options="[
                            '1' => 'فعال',
                            '0' => 'غیرفعال',
                        ]"
                    />
                </x-admin.field>
            </div>

            <div
                wire:key="redirect-form-actions-{{ $this->isEditing ? 'edit' : 'create' }}"
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
                        class="h-4 w-4 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-opacity=".25"
                            stroke-width="4"
                        ></circle>

                        <path
                            d="M22 12a10 10 0 0 0-10-10"
                            stroke="currentColor"
                            stroke-width="4"
                            stroke-linecap="round"
                        ></path>
                    </svg>

                    <span>{{ $this->isEditing ? 'ذخیره تغییرات' : 'ایجاد ریدایرکت' }}</span>
                </x-admin.form.button>

                @if($this->isEditing)
                    <x-admin.form.button
                        type="button"
                        wire:click="cancelEditing"
                        wire:loading.attr="disabled"
                        wire:target="cancelEditing"
                        variant="secondary"
                    >
                        انصراف
                    </x-admin.form.button>
                @endif
            </div>
        </form>
    </x-admin.metabox>
</div>
