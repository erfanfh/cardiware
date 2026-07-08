@php
    use App\Domain\Promotion\Enums\CouponTypeEnum;
    use App\Domain\Promotion\Enums\CouponStatusEnum;
    use App\Domain\Promotion\Enums\CouponScopeEnum;

    $typeOptions = [];
    foreach (CouponTypeEnum::cases() as $type) {
        $typeOptions[$type->value] = $type->label();
    }

    $statusOptions = [];
    foreach (CouponStatusEnum::cases() as $status) {
        $statusOptions[$status->value] = $status->label();
    }

    $scopeOptions = [];
    foreach (CouponScopeEnum::cases() as $scope) {
        $scopeOptions[$scope->value] = $scope->label();
    }

    $coupon = $coupon ?? null;

    $selectedUserIds = old('user_ids', $selectedUserIds ?? []);
    $selectedProductIds = old('product_ids', $selectedProductIds ?? []);
    $selectedStoreIds = old('store_ids', $selectedStoreIds ?? []);

    $appliesToAllUsers = old(
        'applies_to_all_users',
        $coupon?->applies_to_all_users ?? true
    );

    $appliesToAddons = old(
        'applies_to_addons',
        $coupon?->applies_to_addons ?? false
    );

    $selectedScope = old(
        'scope',
        $coupon?->scope?->value ?? CouponScopeEnum::ORDER->value
    );

    $selectedType = old(
        'type',
        $coupon?->type?->value ?? CouponTypeEnum::FIXED_AMOUNT->value
    );

    $selectedProductIds = old('product_ids', $selectedProductIds ?? []);
    $selectedProducts = $selectedProducts ?? [];

    $selectedStoreId = old('store_id', $selectedStoreId ?? null);
    $selectedStore = $selectedStore ?? [];

    $selectedUserIds = old('user_ids', $selectedUserIds ?? []);
    $selectedUsers = $selectedUsers ?? [];
@endphp

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.metabox title="اطلاعات اصلی کدتخفیف">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col gap-4">
                <x-admin.field required label="وضعیت">
                    <x-admin.form.select
                        name="status"
                        placeholder="انتخاب وضعیت"
                        :options="$statusOptions"
                        :value="old('status', $coupon?->status?->value ?? CouponStatusEnum::ACTIVE->value)"
                    />
                </x-admin.field>

                <x-admin.field required label="عنوان">
                    <x-admin.form.input
                        name="title"
                        :value="old('title', $coupon?->title)"
                    />
                </x-admin.field>

                <x-admin.field required label="کد">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="flex-1">
                            <x-admin.form.input
                                id="coupon_code"
                                dir="ltr"
                                name="code"
                                :value="old('code', $coupon?->code)"
                            />
                        </div>

                        <x-admin.form.button
                            type="button"
                            variant="dark"
                            size="md"
                            onclick="generateCouponCode()"
                            class="shrink-0"
                        >
                            @include('icons.reload')

                            تولید خودکار
                        </x-admin.form.button>
                    </div>
                </x-admin.field>
            </div>

            <div>
                <x-admin.field label="توضیحات">
                    <x-admin.form.textarea
                        name="description"
                        rows="7"
                        placeholder="توضیحات داخلی برای مدیران"
                        :value="old('description', $coupon?->description)"
                    />
                </x-admin.field>
            </div>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="نوع و مقدار تخفیف">
        <div
            x-data="{
            couponType: @js($selectedType),
            percentageType: @js(\App\Domain\Promotion\Enums\CouponTypeEnum::PERCENTAGE->value),
            fixedAmountType: @js(\App\Domain\Promotion\Enums\CouponTypeEnum::FIXED_AMOUNT->value),
        }"
            class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >
            <x-admin.field required label="نوع تخفیف">
                <x-admin.form.select
                    name="type"
                    id="coupon_type"
                    :options="$typeOptions"
                    :value="$selectedType"
                    x-on:change="couponType = $event.target.value"
                />
            </x-admin.field>

            <div>
                <label for="coupon_value" class="block text-xs font-medium text-gray-500">
                    <span x-text="couponType === percentageType ? 'درصد تخفیف' : 'مبلغ تخفیف (تومان)'"></span>
                    <span class="text-rose-500">*</span>
                </label>

                <x-admin.form.input
                    id="coupon_value"
                    dir="ltr"
                    type="number"
                    min="0"
                    name="value"
                    :value="old('value', $coupon?->value)"
                />
            </div>

            <div
                x-show="couponType === percentageType"
                x-cloak
            >
                <x-admin.field
                    label="حداکثر مبلغ تخفیف (تومان)"
                    hint="برای مثال: ۲۰٪ تخفیف تا سقف ۱۰۰,۰۰۰ تومان"
                >
                    <x-admin.form.input
                        dir="ltr"
                        type="number"
                        min="0"
                        name="max_discount_amount"
                        :value="old('max_discount_amount', $coupon?->max_discount_amount)"
                    />
                </x-admin.field>
            </div>

            <x-admin.field label="حداقل مبلغ سفارش (تومان)">
                <x-admin.form.input
                    dir="ltr"
                    type="number"
                    min="0"
                    name="minimum_order_amount"
                    :value="old('minimum_order_amount', $coupon?->minimum_order_amount ?? 0)"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="محدودیت زمانی">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin.field label="زمان شروع">
                <x-admin.form.date-picker
                    name="starts_at"
                    :value="old('starts_at', $coupon?->starts_at ? jalali_date($coupon?->starts_at) : '')"
                />
            </x-admin.field>

            <x-admin.field label="زمان پایان">
                <x-admin.form.date-picker
                    name="expires_at"
                    :value="old('expires_at', $coupon?->expires_at ? jalali_date($coupon?->expires_at) : '')"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="محدودیت استفاده">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin.field
                label="تعداد استفاده کلی"
                hint="برای استفاده نامحدود، این فیلد را خالی بگذارید."
            >
                <x-admin.form.input
                    dir="ltr"
                    type="number"
                    min="1"
                    name="usage_limit"
                    :value="old('usage_limit', $coupon?->usage_limit)"
                />
            </x-admin.field>

            <x-admin.field
                label="تعداد استفاده برای هر کاربر"
                hint="برای استفاده نامحدود، این فیلد را خالی بگذارید."
            >
                <x-admin.form.input
                    dir="ltr"
                    type="number"
                    min="1"
                    name="usage_limit_per_user"
                    :value="old('usage_limit_per_user', $coupon?->usage_limit_per_user)"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="کاربران مجاز" class="overflow-visible">
        <div
            x-data="{
                appliesToAllUsers: @js((bool) $appliesToAllUsers),
            }"
            class="grid grid-cols-1 gap-4"
        >
            <label class="cursor-pointer flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <input
                    type="hidden"
                    name="applies_to_all_users"
                    value="0"
                >

                <input
                    id="applies_to_all_users"
                    type="checkbox"
                    name="applies_to_all_users"
                    value="1"
                    x-model="appliesToAllUsers"
                    class="rounded border-slate-300 text-orange-500 focus:ring-orange-200"
                >

                <span class="text-sm font-medium text-slate-700">
                    این کدتخفیف برای همه کاربران قابل استفاده باشد
                </span>
            </label>

            <template x-if="!appliesToAllUsers">
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <x-admin.form.async-select
                        name="user_ids"
                        label="کاربران خاص"
                        :value="$selectedUserIds"
                        :selected-options="$selectedUsers"
                        placeholder="جستجو و انتخاب کاربران..."
                        :multiple="true"
                        :search-url="route('admin.users.ajax-search')"
                        hint="فقط کاربران انتخاب‌شده می‌توانند از این کد تخفیف استفاده کنند."
                    />
                </div>
            </template>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="محدوده اعمال کدتخفیف">
        <div class="grid grid-cols-1 gap-4">
            <x-admin.field required label="محدودیت اعمال">
                <x-admin.form.select
                    name="scope"
                    id="coupon_scope"
                    placeholder="انتخاب محدودیت اعمال"
                    :options="$scopeOptions"
                    :value="$selectedScope"
                />
            </x-admin.field>

            <div
                id="order_scope_hint"
                class="@if($selectedScope !== \App\Domain\Promotion\Enums\CouponScopeEnum::ORDER->value) hidden @endif"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                    این کدتخفیف روی کل سفارش اعمال می‌شود و محدود به محصول یا فروشنده خاصی نیست.
                </div>
            </div>

            <div
                id="products_scope_box"
                class="@if($selectedScope !== \App\Domain\Promotion\Enums\CouponScopeEnum::PRODUCTS->value) hidden @endif"
            >
                <x-admin.field required label="محصولات مجاز">
                    <x-admin.form.async-select
                        name="product_ids"
                        :value="$selectedProductIds"
                        :selected-options="$selectedProducts"
                        placeholder="جستجو و انتخاب محصولات..."
                        :multiple="true"
                        :search-url="route('admin.products.ajax-search')"
                        hint="کدتخفیف فقط روی محصولاتی اعمال می‌شود که اینجا انتخاب شده‌اند."
                    />
                </x-admin.field>
            </div>

            <div
                id="store_scope_box"
                class="@if($selectedScope !== \App\Domain\Promotion\Enums\CouponScopeEnum::STORE->value) hidden @endif"
            >
                <x-admin.field required label="فروشنده مجاز">
                    <x-admin.form.async-select
                        name="store_id"
                        :value="$selectedStoreId"
                        :selected-options="$selectedStore"
                        placeholder="جستجو و انتخاب فروشنده..."
                        :multiple="false"
                        :search-url="route('admin.stores.ajax-search')"
                        hint="کدتخفیف فقط روی محصولات این فروشنده اعمال می‌شود."
                    />
                </x-admin.field>
            </div>

            <label
                class="cursor-pointer flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <input
                    type="hidden"
                    name="applies_to_addons"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="applies_to_addons"
                    value="1"
                    @checked((bool) $appliesToAddons)
                    class="rounded border-slate-300 text-orange-500 focus:ring-orange-200"
                >

                <span class="text-sm font-medium text-slate-700">
                    تخفیف روی خدمات افزوده (Addonها) هم اعمال شود
                </span>
            </label>
        </div>
    </x-admin.metabox>
</x-admin.page>

@push('scripts')
    <script>
        function generateCouponCode() {
            const input = document.getElementById('coupon_code');

            if (!input) {
                return;
            }

            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            const prefix = 'MW';
            const length = 8;

            let code = '';

            if (window.crypto && window.crypto.getRandomValues) {
                const randomValues = new Uint32Array(length);
                window.crypto.getRandomValues(randomValues);

                for (let i = 0; i < length; i++) {
                    code += alphabet[randomValues[i] % alphabet.length];
                }
            } else {
                for (let i = 0; i < length; i++) {
                    code += alphabet[Math.floor(Math.random() * alphabet.length)];
                }
            }

            input.value = `${prefix}-${code}`;
            input.dispatchEvent(new Event('input', {bubbles: true}));
            input.dispatchEvent(new Event('change', {bubbles: true}));

            input.focus();
            input.select();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const scopeSelect = document.getElementById('coupon_scope');

            const orderScopeHint = document.getElementById('order_scope_hint');
            const productsScopeBox = document.getElementById('products_scope_box');
            const storeScopeBox = document.getElementById('store_scope_box');

            function toggleScopeBoxes() {
                if (!scopeSelect) {
                    return;
                }

                const selectedScope = scopeSelect.value;

                orderScopeHint?.classList.toggle('hidden', selectedScope !== 'order');
                productsScopeBox?.classList.toggle('hidden', selectedScope !== 'products');
                storeScopeBox?.classList.toggle('hidden', selectedScope !== 'store');
            }

            scopeSelect?.addEventListener('change', toggleScopeBoxes);

            toggleScopeBoxes();
        });
    </script>
@endpush
