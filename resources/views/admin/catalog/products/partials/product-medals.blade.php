@php
    use App\Domain\Catalog\Models\ProductMedal;

    /*
     * همه مدال‌ها را دریافت می‌کنیم، حتی مدال‌های غیرفعال.
     * چون ممکن است مدالی قبلا به محصول متصل شده باشد و بعدا غیرفعال شود.
     */
    $availableMedals = ProductMedal::query()
        ->with('picture')
        ->orderByDesc('is_active')
        ->orderBy('title')
        ->get();

    $medalMeta = $availableMedals
        ->mapWithKeys(fn (ProductMedal $medal) => [
            (string) $medal->id => [
                'id' => $medal->id,
                'title' => $medal->title,
                'picture_url' => $medal->picture?->url,
                'is_active' => $medal->is_active,
            ],
        ])
        ->all();

    $medalOptions = ['' => 'انتخاب مدال...']
        + $availableMedals
            ->mapWithKeys(fn (ProductMedal $medal) => [
                $medal->id => $medal->title
                    . ($medal->is_active ? '' : ' — غیرفعال'),
            ])
            ->all();

    $items = old('medal_assignments');

    /*
     * بعد از خطای Validation، عنوان مدال را دوباره از دیتابیس
     * به آیتم‌های ارسال‌شده اضافه می‌کنیم تا Heading ریپیتر درست باشد.
     */
    if ($items !== null) {
        $items = collect($items)
            ->map(function ($item) use ($medalMeta): array {
                $productMedalId = data_get(
                    $item,
                    'product_medal_id'
                );

                $meta = $medalMeta[
                    (string) $productMedalId
                ] ?? null;

                return [
                    'id' => data_get($item, 'id'),
                    'product_medal_id' => filled($productMedalId)
                        ? (string) $productMedalId
                        : '',
                    'title' => $meta['title'] ?? '',
                ];
            })
            ->values()
            ->all();
    }

    /*
     * در حالت ویرایش، اتصال‌های فعلی محصول را با ترتیب ثبت‌شده می‌گیریم.
     */
    if (
        $items === null
        && isset($product)
        && $product
    ) {
        $items = $product->medalAssignments()
            ->with([
                'medal.picture',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($assignment) => [
                'id' => $assignment->id,
                'product_medal_id' =>
                    (string) $assignment->product_medal_id,
                'title' => $assignment->medal?->title ?? '',
            ])
            ->values()
            ->toArray();
    }

    $items ??= [];
@endphp

@if($availableMedals->isEmpty())
    <div
        class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
    >
        <span
            class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200"
        >
            <svg
                class="size-7"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9.813 15.904 9 18l-.813-2.096a4.5 4.5 0 0 0-2.59-2.59L3.5 12.5l2.096-.813a4.5 4.5 0 0 0 2.59-2.59L9 7l.813 2.096a4.5 4.5 0 0 0 2.59 2.59l2.097.814-2.096.813a4.5 4.5 0 0 0-2.591 2.591ZM18.259 8.715 18 9.5l-.259-.785a2.25 2.25 0 0 0-1.456-1.456L15.5 7l.785-.259a2.25 2.25 0 0 0 1.456-1.456L18 4.5l.259.785a2.25 2.25 0 0 0 1.456 1.456L20.5 7l-.785.259a2.25 2.25 0 0 0-1.456 1.456Z"
                />
            </svg>
        </span>

        <div class="mt-4 text-sm font-extrabold text-slate-800">
            هنوز مدالی ایجاد نشده است
        </div>

        <p class="mt-2 text-xs leading-6 text-slate-500">
            ابتدا مدال‌های مورد نیاز را ایجاد کنید و سپس آن‌ها را به محصول متصل کنید.
        </p>

        <a
            href="{{ route('admin.products.medals.index') }}"
            class="mt-4 inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-slate-800"
        >
            مدیریت مدال‌های محصولات
        </a>
    </div>
@else
    <div class="space-y-5">
        <x-admin.form.repeater
            name="medal_assignments"
            :items="$items"
            :defaultItem="[
                'id' => null,
                'product_medal_id' => '',
                'title' => '',
            ]"
            addText="افزودن مدال"
            emptyText="هنوز مدالی برای این محصول انتخاب نشده است."
            headingKey="title"
            headingPlaceholder="مدال جدید"
        >
            <div
                class="space-y-4"
                x-data="{
                    medals: @js($medalMeta),

                    get selectedMedal() {
                        const id = String(
                            it.product_medal_id || ''
                        )

                        return this.medals[id] || null
                    },

                    handleMedalChanged() {
                        it.title = this.selectedMedal?.title || ''
                    }
                }"
            >
                <input
                    type="hidden"
                    x-bind:name="field(i, 'id')"
                    x-model="it.id"
                >

                <x-admin.field
                    label="مدال"
                    required
                    hint="هر مدال فقط یک‌بار قابل انتخاب است."
                >
                    <x-admin.form.select
                        x-bind:name="field(i, 'product_medal_id')"
                        x-model="it.product_medal_id"
                        x-on:change="handleMedalChanged()"
                        :options="$medalOptions"
                    />
                </x-admin.field>

                <template x-if="selectedMedal">
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
                    >
                        <div class="flex items-center gap-4 p-4">
                            <div
                                class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm"
                            >
                                <template x-if="selectedMedal.picture_url">
                                    <img
                                        :src="selectedMedal.picture_url"
                                        :alt="selectedMedal.title"
                                        class="h-full w-full object-contain"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </template>

                                <template x-if="!selectedMedal.picture_url">
                                    <svg
                                        class="size-8 text-slate-300"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"
                                        />
                                    </svg>
                                </template>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="truncate text-sm font-black text-slate-900"
                                    x-text="selectedMedal.title"
                                ></div>

                                <div class="mt-2">
                                    <template x-if="selectedMedal.is_active">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700 ring-1 ring-emerald-100"
                                        >
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            فعال و قابل نمایش
                                        </span>
                                    </template>

                                    <template x-if="!selectedMedal.is_active">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-extrabold text-slate-600 ring-1 ring-slate-200"
                                        >
                                            <span class="size-1.5 rounded-full bg-slate-400"></span>
                                            غیرفعال و مخفی در سایت
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div
                            x-show="!selectedMedal.is_active"
                            class="border-t border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold leading-6 text-amber-700"
                        >
                            این مدال به محصول متصل می‌ماند، اما تا زمان فعال‌شدن در صفحه محصول نمایش داده نمی‌شود.
                        </div>
                    </div>
                </template>
            </div>
        </x-admin.form.repeater>

        <div class="flex justify-end">
            <a
                href="{{ route('admin.products.medals.index') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-xs font-extrabold text-brand-700 transition hover:text-brand-800"
            >
                مدیریت مدال‌های محصولات

                <svg
                    class="size-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5M10.5 6H6.75A2.25 2.25 0 0 0 4.5 8.25v9A2.25 2.25 0 0 0 6.75 19.5h9A2.25 2.25 0 0 0 18 17.25V13.5"
                    />
                </svg>
            </a>
        </div>
    </div>
@endif
