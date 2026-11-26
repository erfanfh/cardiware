<x-front.modal
    name="ticket-created"
    title="Ticket submitted successfully"
    description="Your request was sent to the support team."
    max-width="max-w-md"
>
    <div class="space-y-5 text-center">
        <div
            class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                <path
                    d="m5 13 4 4L19 7"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </div>

        <div>
            <div class="text-sm font-black leading-7 text-slate-900 dark:text-white">
                Your message was received
            </div>

            <p class="mx-auto mt-2 max-w-sm text-xs font-medium leading-6 text-slate-500 sm:text-sm sm:leading-7 dark:text-slate-400">
                After the support team reviews it, you can view the reply here.
            </p>
        </div>

        <button
            type="button"
            x-on:click="$dispatch('close-modal', 'ticket-created')"
            class="inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl bg-brand-600 px-5 text-sm font-extrabold text-white shadow-sm shadow-brand-500/20 transition hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-400"
        >
            Great
        </button>
    </div>
</x-front.modal>
