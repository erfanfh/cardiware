<x-front.cart id="comments" class="relative border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <x-front.cart-title
                title="Comments"
                subtitle="What users say about this product"
                iconName="comment-add-01"
            />

            <div class="-mt-3 mr-12">
                @include('front.partials.rating-summary', [
                    'averageRating' => isset($ratingSummary) ? $ratingSummary->averageRating : null,
                    'ratingCount' => isset($ratingSummary) ? $ratingSummary->ratingCount : 0,
                ])
            </div>
        </div>

        <button
            type="button"
            x-data
            @click="$dispatch('open-modal', { name: 'product-send-comment-modal' })"
            class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 hover:opacity-95 active:scale-[0.99] sm:w-auto dark:hover:bg-brand-400"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <path
                    d="M8 10h8M8 14h5M21 11.5c0 4.14-4.03 7.5-9 7.5a10.4 10.4 0 0 1-3.22-.5L3 20l1.7-4.05A7 7 0 0 1 3 11.5C3 7.36 7.03 4 12 4s9 3.36 9 7.5Z"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            Submit comment
        </button>
    </div>

    <div class="mt-5 min-w-0">
        <livewire:front.discussion.product-comments-list
            :commentable="$product"
            :key="'product-comments-list-' . $product->id"
        />
    </div>
</x-front.cart>

<x-front.modal
    name="product-send-comment-modal"
    title="Review product"
    description="Share your experience, opinion or rating of this product."
    maxWidth="max-w-2xl"
>
    <livewire:front.discussion.product-send-comment-form
        :commentable="$product"
        modal-name="product-send-comment-modal"
        :key="'product-send-comment-form-' . $product->id"
    />
</x-front.modal>
