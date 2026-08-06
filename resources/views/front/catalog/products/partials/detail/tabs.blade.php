<section
    class="sticky @can('admin.display') top-[171px] @else top-[131px] @endcan z-30 transition-all duration-300"
    :class="activeSection
        ? 'rounded-2xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900'
        : 'rounded-2xl border border-slate-200 bg-white/90 p-1 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/90'"
>
    <div x-ref="stickyAnchor"></div>

    <div class="relative overflow-x-auto scrollbar-none" x-ref="tabsContainer">
        <div class="flex min-w-max gap-1 sm:min-w-0">
            <template x-for="item in availableSections" :key="item.id">
                <button
                    type="button"
                    @click="scrollTo(item.id)"
                    class="min-w-[92px] flex-1 cursor-pointer whitespace-nowrap rounded-2xl px-3 py-2.5 text-xs font-extrabold transition sm:min-w-0"
                    :class="activeSection === item.id
                        ? 'bg-brand-500 text-slate-50'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'"
                >
                    <span class="flex items-center justify-center gap-1.5">
                        <span x-text="item.label"></span>

                        <template x-if="item.id === 'comments'">
                            <span
                                class="inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-black leading-none"
                                :class="activeSection === item.id
                                    ? 'bg-white/20 text-white'
                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-200'"
                                x-text="formatCommentsCount(commentsCount)"
                            ></span>
                        </template>
                    </span>
                </button>
            </template>
        </div>
    </div>
</section>
