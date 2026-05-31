<div class="sticky top-3 z-10">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-1.5">
        <div class="flex gap-1.5 overflow-x-auto">
            <template x-for="tab in tabs" :key="tab.key">
                <button
                    type="button"
                    @click="activeTab = tab.key"
                    class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-2xl px-4 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeTab === tab.key
                            ? 'bg-brand-600 text-white shadow-sm'
                            : 'text-slate-600 hover:bg-white hover:text-slate-900'"
                >
                    <template x-if="tab.badge !== null && tab.badge > 0">
                            <span
                                class="inline-flex h-6 min-w-6 items-center justify-center rounded-full px-1 text-[11px] font-bold"
                                :class="activeTab === tab.key
                                    ? 'bg-orange-100 text-orange-600'
                                    : 'bg-slate-200 text-slate-600'"
                                x-text="tab.badge"
                            ></span>
                    </template>

                    <span class="whitespace-nowrap" x-text="tab.label"></span>
                </button>
            </template>
        </div>
    </div>
</div>
