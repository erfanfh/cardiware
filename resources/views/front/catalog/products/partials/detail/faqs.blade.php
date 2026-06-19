@if($product->faqs->isNotEmpty())
    <x-front.cart id="faqs">
        <x-front.cart-title
            title="سوالات متداول"
            subtitle="پاسخ به رایج‌ترین پرسش‌های کاربران"
            iconName="user-question-02"
        />

        <div
            class="mt-4 space-y-3 sm:mt-5"
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
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white sm:rounded-3xl">
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-between gap-3 bg-white px-4 py-4 text-right transition hover:bg-slate-50 sm:px-5"
                        @click="toggleFaq({{ $i }})"
                    >
                        <span class="min-w-0 text-sm font-extrabold leading-7 text-slate-900">
                            {{ $faq->question }}
                        </span>

                        <span
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600">
                            <svg
                                class="h-4 w-4 transition-transform duration-300"
                                :class="isOpen({{ $i }}) ? 'rotate-180' : ''"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </button>

                    <div
                        x-show="isOpen({{ $i }})"
                        x-collapse.duration.300ms
                        x-cloak
                        class="bg-slate-50"
                    >
                        <div class="px-4 py-4 text-sm leading-7 text-slate-600 sm:px-5 sm:py-5">
                            {!! nl2br(htmlspecialchars(e($faq->answer))) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-front.cart>
@endif
