@php
    $ticketDepartments = app(\App\Application\Support\Actions\GetActiveTicketDepartmentsAction::class)->execute();
    $ticketProducts = app(\App\Application\Support\Actions\GetTicketFormProductsAction::class)->execute(auth()->user());

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
    title="ثبت تیکت جدید"
    description="درخواست خود را با جزئیات کافی برای تیم پشتیبانی ارسال کنید."
    max-width="max-w-2xl"
>
    <form
        method="POST"
        action="{{ route('account.tickets.store') }}"
        class="space-y-5"
        x-data="{
            departments: @js($departmentOptions),
            products: @js($productOptions),

            selectedDepartmentId: @js((string) old('department_id', '')),
            selectedProductId: @js((string) old('product_id', '')),

            selectedDepartment() {
                return this.departments.find((department) => department.id === this.selectedDepartmentId) || null;
            },

            requiresProduct() {
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
            if (! requiresProduct()) {
                selectedProductId = '';
            }
        "
    >
        @csrf

        <div class="grid grid-cols-2 gap-5 max-md:grid-cols-1">
            <div>
                <label for="ticket-title" class="mb-2 block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                    عنوان تیکت
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
                    دپارتمان
                </label>

                <select
                    id="ticket-department-id"
                    name="department_id"
                    x-model="selectedDepartmentId"
                    class="cursor-pointer h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
                >
                    <option value="">انتخاب دپارتمان</option>

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
        <div x-show="requiresProduct()" x-collapse>
            <label for="ticket-product-id" class="mb-2 block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                محصول مرتبط
            </label>

            <select
                id="ticket-product-id"
                name="product_id"
                x-model="selectedProductId"
                x-bind:required="requiresProduct()"
                class="cursor-pointer h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
            >
                <option value="">انتخاب محصول</option>

                @foreach($ticketProducts as $product)
                    <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
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
                شما هنوز محصولی تهیه نکرده اید.
            </div>
        </div>

        <div>
            <label for="ticket-message" class="mb-2 block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                متن پیام
            </label>

            <textarea
                id="ticket-message"
                name="message"
                rows="5"
                placeholder="لطفاً مشکل یا درخواست خود را کامل توضیح دهید..."
                class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-7 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
            >{{ old('message') }}</textarea>

            @error('message', 'createTicket')
            <div class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div
            class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-end dark:border-slate-800">
            <button
                type="button"
                x-on:click="$dispatch('close-modal', 'create-ticket')"
                class="inline-flex h-11 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
            >
                انصراف
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

                ارسال تیکت
            </button>
        </div>
    </form>
</x-front.modal>
