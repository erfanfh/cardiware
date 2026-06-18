<section
    x-data="productTabs()"
    x-init="init()"
    class="sticky @can('admin.display') top-[171px] @else top-[131px] @endcan z-30 transition-all duration-300"
    :class="activeSection
        ? 'rounded-2xl border border-slate-200 bg-white shadow-sm p-1'
        : 'rounded-none bg-white/80 backdrop-blur border border-slate-200'"
>

    <!-- sticky trigger -->
    <div x-ref="stickyAnchor"></div>

    <div class="relative flex" x-ref="tabsContainer">
        <template x-for="item in availableSections" :key="item.id">

            <button
                type="button"
                @click="scrollTo(item.id)"
                class="cursor-pointer rounded-2xl flex-1 py-2.5 text-xs font-extrabold transition"
                :class="activeSection === item.id
                    ? 'bg-brand-500 text-slate-50'
                    : 'text-slate-500 hover:text-slate-900'"
                x-text="item.label"
            ></button>

        </template>

    </div>

</section>
