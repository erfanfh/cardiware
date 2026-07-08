<div
    wire:key="ticket-department-sidebar-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
    x-data
    x-on:ticket-department-edit-started.window="
        if (window.innerWidth < 1280) {
            $el.scrollIntoView({ behavior: 'smooth', block: 'start' })
        }

        $nextTick(() => {
            setTimeout(() => $refs.ticketDepartmentTitleInput?.focus(), 120)
        })
    "
>
    <x-admin.metabox :collapsible="false" title="اطلاعات دپارتمان">
        <div class="space-y-5">
            <form
                wire:submit.prevent="save"
                wire:key="ticket-department-form-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
                wire:loading.class="opacity-70"
                wire:target="save,cancelEditing"
                class="space-y-5 transition confirmationForm"
            >
                <x-admin.form.simple-validation-errors/>

                <div class="space-y-4">
                    <x-admin.field
                        label="عنوان"
                        for="ticket-department-title"
                    >
                        <x-admin.form.input
                            id="ticket-department-title"
                            x-ref="ticketDepartmentTitleInput"
                            wire:model.defer="title"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="ترتیب نمایش"
                        for="ticket-department-sort-order"
                    >
                        <x-admin.form.input
                            id="ticket-department-sort-order"
                            type="number"
                            min="0"
                            wire:model.defer="sortOrder"
                        />
                    </x-admin.field>

                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input
                                type="checkbox"
                                wire:model.defer="requiresProduct"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            >

                            <span>
                                <span class="block text-sm font-black text-slate-800">
                                    نیازمند انتخاب محصول
                                </span>

                                <span class="mt-1 block text-xs font-bold leading-6 text-slate-500">
                                    اگر فعال باشد، کاربر هنگام ثبت تیکت باید محصول مرتبط را هم انتخاب کند.
                                </span>
                            </span>
                        </label>

                        <div class="border-t border-slate-200"></div>

                        <label class="flex cursor-pointer items-start gap-3">
                            <input
                                type="checkbox"
                                wire:model.defer="isActive"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            >

                            <span>
                                <span class="block text-sm font-black text-slate-800">
                                    فعال
                                </span>

                                <span class="mt-1 block text-xs font-bold leading-6 text-slate-500">
                                    فقط دپارتمان‌های فعال در فرم ثبت تیکت سمت کاربر نمایش داده می‌شوند.
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                <div
                    wire:key="ticket-department-form-actions-{{ $this->isEditing ? 'edit' : 'create' }}"
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
                            {{ $this->isEditing ? 'ذخیره تغییرات' : 'افزودن دپارتمان' }}
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
