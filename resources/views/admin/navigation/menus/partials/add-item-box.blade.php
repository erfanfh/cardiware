<x-admin.metabox :collapsible="false" title="افزودن آیتم به فهرست">
    <div class="border-b border-slate-200 pb-4 mb-4">
        <div class="grid grid-cols-2 gap-2">
            @foreach(\App\Domain\Navigation\Enums\NavigationItemTypeEnum::cases() as $item)
                <button type="button"
                        @click="activeType = '{{ $item->value }}'"
                        :class="activeType === '{{ $item->value }}'
                                            ? 'border-brand-200 bg-brand-50 text-brand-700'
                                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        class="cursor-pointer rounded-2xl border px-3 py-2.5 text-xs font-extrabold transition">
                    {{ $item->label() }}
                </button>
            @endforeach
        </div>
    </div>

    <div>
        <div x-show="activeType === 'custom_link'" x-transition.opacity.duration.150ms
             class="space-y-4">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">عنوان لینک</label>
                <input x-model="customLink.title" type="text" placeholder="مثلاً دانلودها"
                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">آدرس لینک</label>
                <input x-model="customLink.url" type="text" placeholder="/downloads"
                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
            </div>

            <button type="button"
                    @click="addCustomLink()"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-brand-600 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-brand-700">
                افزودن به فهرست
            </button>
        </div>

        <div x-show="isSearchableEntityType(activeType)" x-transition.opacity.duration.150ms class="space-y-4">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700" x-text="entityPicker.label"></label>

                <div class="relative">
                    <input
                        x-model="entityPicker.search"
                        @input="onEntitySearchInput()"
                        type="text"
                        placeholder="حداقل ۲ حرف وارد کنید"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                    >

                    <button
                        x-show="entityPicker.search"
                        type="button"
                        @click="resetEntitySearch()"
                        class="absolute left-3 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs font-bold text-slate-500 hover:bg-slate-100"
                    >
                        پاک
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-500" x-show="!entityPicker.searched"
                      x-text="entityPicker.topText"></span>

                <span class="font-bold text-slate-500" x-show="entityPicker.searched">
            نتایج جستجو
        </span>

                <span class="font-bold text-brand-600" x-show="entityPicker.loading">
            در حال جستجو...
        </span>
            </div>

            <div class="space-y-2">
                <template x-if="!entityPicker.loading && entityPicker.items.length === 0">
                    <div
                        class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                <span x-show="entityPicker.search.length < 2">
                    برای جستجو حداقل ۲ حرف وارد کنید.
                </span>

                        <span x-show="entityPicker.search.length >= 2" x-text="entityPicker.emptyText"></span>
                    </div>
                </template>

                <template x-for="item in entityPicker.items" :key="`${activeType}-${item.id}`">
                    <button type="button"
                            @click="addEntityItem(activeType, item)"
                            class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right transition hover:border-brand-200 hover:bg-brand-50/40">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-black text-slate-900" x-text="item.title"></div>

                            <template x-if="item.path">
                                <div class="mt-1 truncate text-xs text-slate-500 dir-ltr text-left"
                                     x-text="item.path"></div>
                            </template>
                        </div>

                        <span
                            class="mr-3 shrink-0 rounded-xl bg-brand-50 px-3 py-1 text-xs font-extrabold text-brand-700">
                    افزودن
                </span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</x-admin.metabox>
