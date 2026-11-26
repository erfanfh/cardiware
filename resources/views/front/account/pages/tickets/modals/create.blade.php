@php
    $ticketDepartments = app(\App\Application\Support\Actions\GetActiveTicketDepartmentsAction::class)->execute();
    $ticketProducts = app(\App\Application\Support\Actions\GetTicketFormProductsAction::class)->execute(auth()->user());
    $isVipMember = auth()->user()?->hasActiveVipSubscription();

    $departmentOptions = $ticketDepartments
        ->map(fn ($department) => [
            'id' => (string) $department->id,
            'title' => $department->title,
            'description' => $department->description,
            'requires_product' => (bool) $department->requires_product,
        ])
        ->values();

    $productOptions = $ticketProducts
        ->map(fn ($product) => [
            'id' => (string) $product->id,
            'title' => $product->title,
        ])
        ->values();
@endphp

<x-front.modal
    name="create-ticket"
    title="Create ticket"
    description="Send your request to the support team with sufficient detail."
    max-width="max-w-2xl"
>
    <form
        method="POST"
        action="{{ route('account.tickets.store') }}"
        enctype="multipart/form-data"
        class="space-y-5"
        x-data="{
            departments: @js($departmentOptions),
            products: @js($productOptions),
            isVip: @js((bool) $isVipMember),

            selectedDepartmentId: @js((string) old('department_id', '')),
            selectedProductId: @js((string) old('product_id', '')),

            selectedDepartment() {
                return this.departments.find((department) => department.id === this.selectedDepartmentId) || null;
            },

            requiresProduct() {
                return ! this.isVip && this.selectedDepartment()?.requires_product === true;
            },

            departmentRequiresProduct() {
                return this.selectedDepartment()?.requires_product === true;
            },

            selectedDepartmentDescription() {
                return this.selectedDepartment()?.description || null;
            },

            hasProducts() {
                return this.products.length > 0;
            }
        }"
        x-effect="
            if (! isVip && ! requiresProduct()) {
                selectedProductId = '';
            }
        "
    >
        @csrf

        @php
            $tips = get_setting('ticket', 'ticket_submission_tips');
        @endphp
        @if(count($tips) > 0)
            <div
                class="relative overflow-hidden rounded-3xl border border-sky-200/80 bg-gradient-to-br from-sky-50 via-white to-indigo-50/70 p-4 shadow-sm dark:border-sky-500/20 dark:from-sky-500/10 dark:via-slate-900 dark:to-indigo-500/10 sm:p-5"
            >
                {{-- Decorative background --}}
                <div
                    class="pointer-events-none absolute -left-10 -top-12 h-32 w-32 rounded-full bg-sky-300/20 blur-3xl dark:bg-sky-400/10"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-14 -right-8 h-32 w-32 rounded-full bg-indigo-300/20 blur-3xl dark:bg-indigo-400/10"
                ></div>

                <div class="relative">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 ring-1 ring-inset ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/20"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M12 8v4m0 4h.01M10.29 3.86 2.82 16.5A2 2 0 0 0 4.54 19.5h14.92a2 2 0 0 0 1.72-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-black text-slate-800 dark:text-white">
                                Before submitting a ticket
                            </h3>

                            <p class="mt-1 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                                Following these tips helps us review your request faster and more accurately.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-2">
                        @foreach($tips as $tip)
                            <div
                                class="flex items-center gap-2.5 rounded-2xl border border-white/80 bg-white/70 px-3 py-2.5 shadow-sm backdrop-blur-sm dark:border-white/5 dark:bg-white/[0.04]"
                            >
                                <div
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="m5 12 4 4L19 6"
                                            stroke="currentColor"
                                            stroke-width="2.2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <p class="min-w-0 text-xs font-bold leading-6 text-slate-600 dark:text-slate-300">
                                    {{ $tip['text'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <div class="grid grid-cols-2 gap-5 max-md:grid-cols-1">
            <div>
                <label for="ticket-title"
                       class="mb-2 block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                    Ticket subject
                </label>

                <input
                    id="ticket-title"
                    name="title"
                    type="text"
                    value="{{ old('title') }}"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
                >

                @error('title', 'createTicket')
                <div class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div>
                <label for="ticket-department-id"
                       class="mb-2 block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                    Department
                </label>

                <select
                    id="ticket-department-id"
                    name="department_id"
                    x-model="selectedDepartmentId"
                    class="cursor-pointer h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
                >
                    <option value="">Select department</option>

                    @foreach($ticketDepartments as $department)
                        <option
                            value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>
                            {{ $department->title }}
                        </option>
                    @endforeach
                </select>

                @error('department_id', 'createTicket')
                <div class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                    {{ $message }}
                </div>
                @enderror

                <div
                    x-show="selectedDepartmentDescription()"
                    x-collapse
                    class="mt-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold leading-6 text-slate-500 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400"
                >
                    <span x-text="selectedDepartmentDescription()"></span>
                </div>
            </div>
        </div>
        <div x-show="departmentRequiresProduct()" x-collapse>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <label for="ticket-product-id"
                       class="block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                    Related product
                </label>

                <span x-show="isVip"
                      class="inline-flex items-center gap-1 rounded-lg border border-amber-300 bg-amber-50 px-2 py-0.5 text-[10px] font-extrabold text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                    👑 Exclusive VIP: Product selection is optional
                </span>
            </div>

            <select
                id="ticket-product-id"
                name="product_id"
                x-model="selectedProductId"
                x-bind:required="requiresProduct()"
                class="cursor-pointer h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
            >
                <option value="">Select product</option>

                @foreach($ticketProducts as $product)
                    <option
                        value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                        {{ $product->title }}
                    </option>
                @endforeach
            </select>

            @error('product_id', 'createTicket')
            <div class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                {{ $message }}
            </div>
            @enderror

            <div
                x-show="requiresProduct() && ! hasProducts()"
                x-collapse
                class="mt-2 rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold leading-6 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200"
            >
                You have not purchased any products yet.
            </div>
        </div>

        <div>
            <label for="ticket-message"
                   class="mb-2 block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                Message
            </label>

            <textarea
                id="ticket-message"
                name="message"
                rows="5"
                placeholder="Please describe your issue or request fully..."
                class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-7 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
            >{{ old('message') }}</textarea>

            @error('message', 'createTicket')
            <div class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                {{ $message }}
            </div>
            @enderror
        </div>

        <x-front.ticket-image-upload error-bag="createTicket" />

        <div
            class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-end dark:border-slate-800">
            <button
                type="button"
                x-on:click="$dispatch('close-modal', 'create-ticket')"
                class="inline-flex h-11 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
            >
                Cancel
            </button>

            <button
                type="submit"
                class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 text-sm font-extrabold text-white shadow-sm shadow-brand-500/20 transition hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-500/25 focus:outline-none focus:ring-4 focus:ring-brand-100 dark:bg-brand-500 dark:hover:bg-brand-400 dark:focus:ring-brand-500/20"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M21 12 3 20l3-8-3-8 18 8ZM6 12h7"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                Submit ticket
            </button>
        </div>
    </form>
</x-front.modal>
