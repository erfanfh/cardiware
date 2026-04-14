<x-admin.metabox :collapsible="false" title="افزودن آیتم به فهرست">
    <div class="mb-4 border-b border-slate-200 pb-4">
        <div class="grid grid-cols-2 gap-2">
            @foreach(\App\Domain\Navigation\Enums\NavigationItemTypeEnum::cases() as $item)
                <button type="button"
                        @click="activeType = '{{ $item->value }}'; resetEntitySearch()"
                        :class="activeType === '{{ $item->value }}'
                            ? 'border-brand-200 bg-brand-50 text-brand-700 shadow-sm'
                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        class="cursor-pointer rounded-2xl border px-3 py-2.5 text-xs font-extrabold transition">
                    {{ $item->label() }}
                </button>
            @endforeach
        </div>
    </div>

    <div>
        <div
            x-show="activeType === 'custom_link'"
            x-transition.opacity.duration.150ms
            @keydown.enter.prevent="addCustomLink()"
            class="space-y-4"
        >
            <x-admin.field
                class="col-span-12"
                label="عنوان لینک"
            >
                <x-admin.form.input x-model="customLink.title"/>
            </x-admin.field>

            <x-admin.field
                class="col-span-12"
                label="آدرس لینک"
            >
                <x-admin.form.input
                    x-model="customLink.url"
                    class="direction-ltr"
                />
            </x-admin.field>

            <div class="flex flex-row-reverse">
                <x-admin.form.button @click="addCustomLink()">
                    افزودن به فهرست
                </x-admin.form.button>
            </div>
        </div>

        <div
            x-show="isSearchableEntityType(activeType)"
            x-transition.opacity.duration.150ms
            class="space-y-4"
        >
            <div class="mb-5">
                <label class="mb-2 block text-xs font-bold text-slate-500" x-text="entityPicker.label"></label>

                <div class="relative">
                    <x-admin.field class="w-full">
                        <x-admin.form.input
                            x-model="entityPicker.search"
                            @input="onEntitySearchInput()"
                            placeholder="حداقل ۲ حرف وارد کنید"
                            class="pl-20 placeholder:text-xs"
                        />
                    </x-admin.field>

                    <div class="absolute inset-y-0 left-3 flex items-center gap-2">
                        <template x-if="entityPicker.loading">
                            <span
                                class="inline-flex items-center gap-2 rounded-xl bg-brand-50 px-2.5 py-1 text-[11px] font-bold text-brand-700">
                                <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25"
                                            stroke-width="4"></circle>
                                    <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                                          stroke-linecap="round"></path>
                                </svg>
                                جستجو...
                            </span>
                        </template>

                        <button
                            x-show="entityPicker.search"
                            type="button"
                            @click="resetEntitySearch()"
                            class="cursor-pointer rounded-lg px-2 py-1 text-xs font-bold text-slate-500 transition hover:bg-slate-100"
                        >
                            پاک
                        </button>
                    </div>
                </div>
            </div>

            <div class="mb-3 flex items-center justify-between text-xs">
                <span
                    class="font-bold text-slate-500"
                    x-show="!entityPicker.loading && !entityPicker.searched"
                    x-text="entityPicker.topText"
                ></span>

                <span
                    class="font-bold text-slate-500"
                    x-show="!entityPicker.loading && entityPicker.searched && entityPicker.items.length > 0"
                >
                    نتایج جستجو
                </span>

                <span
                    class="font-bold text-brand-600"
                    x-show="entityPicker.loading"
                >
                    در حال دریافت نتایج...
                </span>
            </div>

            <div class="space-y-2">
                {{-- Loading State --}}
                <template x-if="entityPicker.loading">
                    <div class="space-y-2">
                        <template x-for="i in 4" :key="i">
                            <div class="animate-pulse rounded-2xl border border-slate-200 bg-white p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="h-3.5 w-2/3 rounded-lg bg-slate-200"></div>
                                        <div class="mt-2 h-3 w-1/3 rounded-lg bg-slate-100"></div>
                                    </div>

                                    <div class="h-8 w-16 rounded-xl bg-slate-100"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Empty State --}}
                <template x-if="!entityPicker.loading && entityPicker.items.length === 0">
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                        <div
                            class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-2xl bg-white shadow-sm">
                            <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M21 21L15.8 15.8M18 10.5C18 14.6421 14.6421 18 10.5 18C6.35786 18 3 14.6421 3 10.5C3 6.35786 6.35786 3 10.5 3C14.6421 3 18 6.35786 18 10.5Z"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div class="text-sm font-bold text-slate-600">
                            <span x-show="entityPicker.search.length < 2">
                                برای جستجو حداقل ۲ حرف وارد کنید.
                            </span>

                            <span x-show="entityPicker.search.length >= 2" x-text="entityPicker.emptyText"></span>
                        </div>
                    </div>
                </template>

                {{-- Results --}}
                <template x-if="!entityPicker.loading && entityPicker.items.length > 0">
                    <div class="space-y-2">
                        <template x-for="item in entityPicker.items" :key="`${activeType}-${item.id}`">
                            <button type="button"
                                    @click="addEntityItem(activeType, item)"
                                    class="group flex w-full cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-white p-3 text-right transition hover:border-brand-200 hover:bg-brand-50/40 hover:shadow-sm">
                                <div class="min-w-0">
                                    <div
                                        class="truncate text-[0.82rem] font-black text-slate-900 group-hover:text-brand-700"
                                        x-text="item.title"></div>
                                </div>

                                <span
                                    class="mr-3 shrink-0 rounded-xl bg-brand-50 px-3 py-1 text-[0.7rem] font-bold text-brand-700 transition group-hover:bg-brand-100">
                                    افزودن
                                </span>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-admin.metabox>
