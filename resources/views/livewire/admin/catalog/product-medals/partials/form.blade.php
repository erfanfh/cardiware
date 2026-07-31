<div
    wire:key="product-medal-sidebar-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
    x-data
    x-on:product-medal-edit-started.window="
        if (window.innerWidth < 1280) {
            $el.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            })
        }

        $nextTick(() => {
            setTimeout(
                () => $refs.productMedalTitleInput?.focus(),
                120
            )
        })
    "
>
    <x-admin.metabox
        :collapsible="false"
        title="اطلاعات مدال محصول"
    >
        <form
            wire:submit.prevent="save"
            wire:key="product-medal-form-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
            wire:loading.class="opacity-70"
            wire:target="save,cancelEditing"
            class="space-y-5 transition confirmationForm"
        >
            <x-admin.form.simple-validation-errors/>

            <div class="space-y-4">
                <x-admin.field
                    label="عنوان مدال"
                    for="product-medal-title"
                >
                    <x-admin.form.input
                        id="product-medal-title"
                        x-ref="productMedalTitleInput"
                        wire:model.defer="title"
                    />
                </x-admin.field>

                <x-admin.field label="تصویر مدال">
                    <div
                        x-data
                        x-on:mfip-selected.window="
                            $wire.set(
                                'pictureId',
                                Number($event.detail.mediaId) || null
                            );

                            $wire.set(
                                'picturePath',
                                $event.detail.mediaPath || null
                            );
                        "
                        x-on:mfip-cleared.window="
                            $wire.set('pictureId', null);
                            $wire.set('picturePath', null);
                        "
                    >
                        <x-admin.form.media-picker
                            mode="field"
                            name="picture"
                            :value="[
                                'id' => $pictureId,
                                'path' => $picturePath,
                            ]"
                            layout="vertical"
                            previewHeight="h-36"
                        />
                    </div>
                </x-admin.field>

                <x-admin.field label="وضعیت">
                    <x-admin.form.select
                        wire:model.defer="isActive"
                        :options="[
                            '1' => 'فعال',
                            '0' => 'غیرفعال',
                        ]"
                    />
                </x-admin.field>
            </div>

            <div
                wire:key="product-medal-form-actions-{{ $this->isEditing ? 'edit' : 'create' }}"
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

                    <span>
                        {{ $this->isEditing
                            ? 'ذخیره تغییرات'
                            : 'افزودن مدال'
                        }}
                    </span>
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
