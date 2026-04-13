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

        <div x-show="activeType === 'category'" x-transition.opacity.duration.150ms
             class="space-y-2">
            <template x-for="item in categories" :key="'category-' + item.id">
                <button type="button"
                        @click="addEntityItem('category', item)"
                        class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right transition hover:border-brand-200 hover:bg-brand-50/40">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-black text-slate-900"
                             x-text="item.title"></div>
                        <div class="mt-1 truncate text-xs text-slate-500 dir-ltr text-left"
                             x-text="item.path"></div>
                    </div>
                    <span
                        class="mr-3 shrink-0 rounded-xl bg-brand-50 px-3 py-1 text-xs font-extrabold text-brand-700">
                                            افزودن
                                        </span>
                </button>
            </template>
        </div>

        <div x-show="activeType === 'tag'" x-transition.opacity.duration.150ms
             class="space-y-2">
            <template x-for="item in tags" :key="'tag-' + item.id">
                <button type="button"
                        @click="addEntityItem('tag', item)"
                        class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right transition hover:border-brand-200 hover:bg-brand-50/40">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-black text-slate-900"
                             x-text="item.title"></div>
                        <div class="mt-1 truncate text-xs text-slate-500 dir-ltr text-left"
                             x-text="item.path"></div>
                    </div>
                    <span
                        class="mr-3 shrink-0 rounded-xl bg-brand-50 px-3 py-1 text-xs font-extrabold text-brand-700">
                                            افزودن
                                        </span>
                </button>
            </template>
        </div>

        <div x-show="activeType === 'post'" x-transition.opacity.duration.150ms
             class="space-y-2">
            <template x-for="item in posts" :key="'post-' + item.id">
                <button type="button"
                        @click="addEntityItem('post', item)"
                        class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right transition hover:border-brand-200 hover:bg-brand-50/40">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-black text-slate-900"
                             x-text="item.title"></div>
                        <div class="mt-1 truncate text-xs text-slate-500 dir-ltr text-left"
                             x-text="item.path"></div>
                    </div>
                    <span
                        class="mr-3 shrink-0 rounded-xl bg-brand-50 px-3 py-1 text-xs font-extrabold text-brand-700">
                                            افزودن
                                        </span>
                </button>
            </template>
        </div>

        <div x-show="activeType === 'page'" x-transition.opacity.duration.150ms
             class="space-y-2">
            <template x-for="item in pages" :key="'page-' + item.id">
                <button type="button"
                        @click="addEntityItem('page', item)"
                        class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right transition hover:border-brand-200 hover:bg-brand-50/40">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-black text-slate-900"
                             x-text="item.title"></div>
                        <div class="mt-1 truncate text-xs text-slate-500 dir-ltr text-left"
                             x-text="item.path"></div>
                    </div>
                    <span
                        class="mr-3 shrink-0 rounded-xl bg-brand-50 px-3 py-1 text-xs font-extrabold text-brand-700">
                                            افزودن
                                        </span>
                </button>
            </template>
        </div>

        <div x-show="activeType === 'project'" x-transition.opacity.duration.150ms
             class="space-y-2">
            <template x-for="item in projects" :key="'project-' + item.id">
                <button type="button"
                        @click="addEntityItem('project', item)"
                        class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right transition hover:border-brand-200 hover:bg-brand-50/40">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-black text-slate-900"
                             x-text="item.title"></div>
                        <div class="mt-1 truncate text-xs text-slate-500 dir-ltr text-left"
                             x-text="item.path"></div>
                    </div>
                    <span
                        class="mr-3 shrink-0 rounded-xl bg-brand-50 px-3 py-1 text-xs font-extrabold text-brand-700">
                                            افزودن
                                        </span>
                </button>
            </template>
        </div>
    </div>
</x-admin.metabox>
