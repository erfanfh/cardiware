@if($product->faqs->isNotEmpty())
    <x-front.cart id="faqs">
        <x-front.cart-title
            title="سوالات متداول"
            subtitle="پاسخ به رایج‌ترین پرسش‌های کاربران"
            iconName="user-question-02"
        />

        <div
            class="mt-5 space-y-3"
            x-data="{
                openFaq: null,

                toggleFaq(index) {
                    this.openFaq = this.openFaq === index ? null : index;
                },

                isOpen(index) {
                    return this.openFaq === index;
                }
            }"
        >
            @foreach($product->faqs as $i => $faq)
                <div class="rounded-3xl border border-slate-200 overflow-hidden bg-white">
                    <button
                        type="button"
                        class="cursor-pointer w-full flex items-center justify-between gap-3 px-4 sm:px-5 py-4 bg-white hover:bg-slate-50 transition text-right"
                        @click="toggleFaq({{ $i }})"
                    >
                        <span class="text-sm font-extrabold text-slate-900">
                            {{ $faq->question }}
                        </span>

                        <span
                            class="shrink-0 h-9 w-9 rounded-2xl border border-slate-200 bg-slate-50 grid place-items-center text-slate-600">
                            <svg
                                class="h-4 w-4 transition-transform duration-300"
                                :class="isOpen({{ $i }}) ? 'rotate-180' : ''"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M6 9l6 6 6-6"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </span>
                    </button>

                    <div
                        x-show="isOpen({{ $i }})"
                        x-collapse.duration.300ms
                        x-cloak
                        class="bg-slate-50"
                    >
                        <div class="px-4 sm:px-5 py-5 text-sm text-slate-600 leading-7">
                            {!! nl2br(htmlspecialchars(e($faq->answer))) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-front.cart>
@endif
