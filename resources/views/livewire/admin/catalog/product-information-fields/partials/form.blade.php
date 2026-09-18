<div
        class="scroll-mt-20"
        data-admin-inline-edit-form
        wire:key="product-information-field-sidebar-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
>
    <x-admin.metabox :collapsible="false" title="اطلاعات فیلد محصول">
        <div class="space-y-5">
            <form
                    wire:submit.prevent="save"
                    wire:key="product-information-field-form-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
                    wire:loading.class="opacity-70"
                    wire:target="save,cancelEditing"
                    class="space-y-5 transition confirmationForm"
            >
                <x-admin.form.simple-validation-errors/>

                <div class="space-y-4">
                    <x-admin.field
                            label="عنوان"
                            for="product-information-field-title"
                    >
                        <x-admin.form.input
                                id="product-information-field-title"
                                x-ref="productInformationFieldTitleInput"
                                wire:model.defer="title"
                        />
                    </x-admin.field>

                    <x-admin.field label="آیکون">
                        <x-admin.form.icon-picker
                                name="icon"
                                :value="$icon"
                                :id-value="$iconId"
                                livewire-model="icon"
                                livewire-id-model="iconId"
                                hint="اختیاری است؛ در صورت نیاز یک آیکون برای این فیلد انتخاب کنید."
                        />
                    </x-admin.field>
                </div>

                <div
                        wire:key="product-information-field-form-actions-{{ $this->isEditing ? 'edit' : 'create' }}"
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
                            {{ $this->isEditing ? 'ذخیره تغییرات' : 'افزودن فیلد' }}
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
