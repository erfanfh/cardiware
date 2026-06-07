<section class="mt-6 lg:hidden">
    <div
        class="overflow-x-auto snap-x snap-mandatory pb-3 pr-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div class="flex gap-3 w-max">
            <button @click="setTab('dashboard')"
                    :class="activeTab === 'dashboard' ? 'border-brand-100 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800'"
                    class="snap-start h-10 px-4 rounded-2xl border font-extrabold transition">داشبورد
            </button>
            <button @click="setTab('downloads')"
                    :class="activeTab === 'downloads' ? 'border-brand-100 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800'"
                    class="snap-start h-10 px-4 rounded-2xl border font-extrabold transition">دانلودها
            </button>
            <button @click="setTab('orders')"
                    :class="activeTab === 'orders' ? 'border-brand-100 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800'"
                    class="snap-start h-10 px-4 rounded-2xl border font-extrabold transition">سفارش‌ها
            </button>
            <button @click="setTab('profile')"
                    :class="activeTab === 'profile' ? 'border-brand-100 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800'"
                    class="snap-start h-10 px-4 rounded-2xl border font-extrabold transition">اطلاعات حساب
            </button>
            <button @click="setTab('tickets')"
                    :class="activeTab === 'tickets' ? 'border-brand-100 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800'"
                    class="snap-start h-10 px-4 rounded-2xl border font-extrabold transition">تیکت‌ها
            </button>
            <button @click="setTab('favorites')"
                    :class="activeTab === 'favorites' ? 'border-brand-100 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800'"
                    class="snap-start h-10 px-4 rounded-2xl border font-extrabold transition">علاقه‌مندی‌ها
            </button>
            <button @click="setTab('security')"
                    :class="activeTab === 'security' ? 'border-brand-100 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800'"
                    class="snap-start h-10 px-4 rounded-2xl border font-extrabold transition">امنیت
            </button>
        </div>
    </div>
</section>
