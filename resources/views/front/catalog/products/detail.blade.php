@extends('front.master')

@section('content')
    <main
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8"
        x-data="productTabs()"
        x-init="init()"
        :style="`--header-offset: ${headerOffset}px`"
    >
        <section data-smart-sidebar-layout class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div data-smart-sidebar-content class="lg:col-span-7 space-y-6">
                @include('front.catalog.products.partials.detail.title')
                @include('front.catalog.products.partials.detail.share-box')
                @include('front.catalog.products.partials.detail.image-gallery')

                @include('front.catalog.products.partials.detail.tabs')

                @include('front.catalog.products.partials.detail.content')
                @include('front.catalog.products.partials.detail.installation-steps')
                @include('front.catalog.products.partials.detail.faqs')
                @include('front.catalog.products.partials.detail.releases')
                @include('front.catalog.products.partials.detail.comments')
                @include('front.catalog.products.partials.detail.related-products')
            </div>

            <div data-smart-sidebar-column class="lg:col-span-3">
                <aside data-smart-sidebar-box class="space-y-6">
                    <!-- price box -->
                    <livewire:front.catalog.product-buy-box :product="$product"/>

                    <!-- update progress -->
                    {{--                    @include('front.catalog.products.partials.detail.sidebar.update-progress')--}}

                    <!-- product info -->
                    <x-front.cart>
                        <div class="text-sm font-extrabold text-slate-900 mb-4">باکس اطلاعات محصول</div>

                        <div class="space-y-3 [&_svg]:w-4 [&_svg]:h-4">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500 flex items-center gap-1.5">
                                    @include('icons.view')
                                    <span>تعداد بازدید</span>
                                </div>
                                <div class="text-sm font-bold text-slate-900">
                                    {{ number_format(page_views($product)) }}
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500 flex items-center gap-1.5">
                                    @include('icons.shopping-basket-03')
                                    <span>تعداد فروش</span>
                                </div>
                                <div class="text-sm font-bold text-slate-900">
                                    {{ $soldQuantity ? number_format($soldQuantity) : 'جدید!' }}
                                </div>
                            </div>

                            @foreach($product->informationEntries as $info)
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                    <div class="text-xs font-extrabold text-slate-500 flex items-center gap-1.5">
                                        @include('icons.menu-01')
                                        <span>{{ $info->field->title }}</span>
                                    </div>
                                    <div class="text-sm font-bold text-slate-900">
                                        {{ $info->value }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-front.cart>

                    @include('front.catalog.products.partials.detail.sidebar.store')

                    @if($product->tags)
                        <x-front.cart>
                            <div class="text-sm font-extrabold text-slate-900 mb-4">برچسب‌ها</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->tags as $tag)
                                    <a href="#"
                                       class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-3 py-2 rounded-xl hover:bg-brand-50 hover:border-brand-100 hover:text-brand-800 transition">
                                        {{ $tag->title }}
                                    </a>
                                @endforeach
                            </div>
                        </x-front.cart>
                    @endif

                    <!-- support card -->
                    {{--                    <section--}}
                    {{--                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden">--}}
                    {{--                        <div--}}
                    {{--                            class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>--}}
                    {{--                        <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>--}}

                    {{--                        <div class="relative">--}}
                    {{--                            <div class="text-sm font-extrabold text-slate-900">--}}
                    {{--                                نیاز به راهنمایی قبل از خرید داری؟--}}
                    {{--                            </div>--}}

                    {{--                            <p class="mt-2 text-sm text-slate-600 leading-7">--}}
                    {{--                                اگر در انتخاب نسخه مناسب، روش نصب یا سازگاری محصول سوال داری...--}}
                    {{--                            </p>--}}

                    {{--                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">--}}
                    {{--                                <a href="#"--}}
                    {{--                                   class="rounded-2xl bg-slate-900 text-white px-4 py-3 text-sm font-extrabold text-center">--}}
                    {{--                                    ثبت تیکت--}}
                    {{--                                </a>--}}

                    {{--                                <a href="#"--}}
                    {{--                                   class="rounded-2xl border border-slate-200 text-brand-700 px-4 py-3 text-sm font-extrabold text-center hover:bg-brand-50">--}}
                    {{--                                    پیام به فروشنده--}}
                    {{--                                </a>--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}
                    {{--                    </section>--}}
                </aside>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        function productTabs() {
            return {

                activeSection: 'product-content',
                activeIndex: 0,
                isSticky: false,

                headerBase: 130,
                adminBar: 41,
                isAdminBarVisible: false,

                sections: [
                    {id: 'product-content', label: 'توضیحات'},
                    {id: 'installation-steps', label: 'راهنمای نصب'},
                    {id: 'faqs', label: 'سوالات متداول'},
                    {id: 'releases', label: 'تغییرات'},
                    {id: 'comments', label: 'دیدگاه‌ها'},
                ],

                init() {
                    this.$nextTick(() => {
                        this.observeSections();
                        this.initSticky();
                        this.updateIndex();
                    });
                },

                get headerOffset() {
                    return this.headerBase + (this.isAdminBarVisible ? this.adminBar : 0);
                },

                get availableSections() {
                    return this.sections.filter(s => document.getElementById(s.id));
                },

                scrollTo(id) {
                    const el = document.getElementById(id);
                    if (!el) return;

                    const top = el.getBoundingClientRect().top + window.scrollY;

                    window.scrollTo({
                        top: top - this.headerOffset - 12,
                        behavior: 'smooth'
                    });

                    this.activeSection = id;
                    this.updateIndex();
                },

                updateIndex() {
                    const index = this.availableSections.findIndex(
                        s => s.id === this.activeSection
                    );

                    this.activeIndex = index >= 0 ? index : 0;
                },

                observeSections() {
                    const observer = new IntersectionObserver(
                        (entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    this.activeSection = entry.target.id;
                                    this.updateIndex();
                                }
                            });
                        },
                        {
                            root: null,
                            rootMargin: `-${this.headerOffset}px 0px -55% 0px`,
                            threshold: 0.15
                        }
                    );

                    this.sections.forEach(s => {
                        const el = document.getElementById(s.id);
                        if (el) observer.observe(el);
                    });
                },

                initSticky() {
                    const el = this.$refs.stickyAnchor;
                    if (!el) return;

                    const observer = new IntersectionObserver(
                        ([entry]) => {
                            this.isSticky = !entry.isIntersecting;
                        },
                        {threshold: 1}
                    );

                    observer.observe(el);
                },
            }
        }
    </script>
@endpush
