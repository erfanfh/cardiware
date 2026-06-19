<section
    x-data="productTabs()"
    x-init="init()"
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
                    x-text="item.label"
                ></button>
            </template>
        </div>
    </div>
</section>
