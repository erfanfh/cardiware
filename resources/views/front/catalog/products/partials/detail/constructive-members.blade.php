<section id="constructive-members"
         class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm scroll-mt-36">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">👥</span>
            <div>
                <h2 class="text-base font-extrabold">اعضای سازنده</h2>
                <p class="text-xs text-slate-500 mt-1">برای دیدن اطلاعات هر عضو کلیک کنید</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <template x-for="member in teamMembers" :key="member.name">
            <button
                type="button"
                @click="openMember(member)"
                class="rounded-3xl border border-slate-200 bg-white p-4 text-right hover:shadow-soft transition"
            >
                <img :src="member.avatar" alt="" class="h-20 w-20 rounded-2xl object-cover mx-auto">
                <div class="mt-4 text-sm font-extrabold text-slate-900 text-center"
                     x-text="member.name"></div>
                <div class="mt-1 text-xs text-slate-500 text-center" x-text="member.role"></div>
            </button>
        </template>
    </div>
</section>
