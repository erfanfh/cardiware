<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox
                    :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($productIds) . ')'"
                    :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($productIds) . ')'"
                />
            </x-admin.list.th>

            <x-admin.list.th width="90px">تصویر</x-admin.list.th>

            <x-admin.list.th>عنوان</x-admin.list.th>

            <x-admin.list.th width="150px">شناسه محصول</x-admin.list.th>

            <x-admin.list.th>فروشنده</x-admin.list.th>

            <x-admin.list.th width="130px">مبلغ</x-admin.list.th>

            <x-admin.list.th width="130px">نوع</x-admin.list.th>

            <x-admin.list.th width="130px">ساختار</x-admin.list.th>

            <x-admin.list.th width="140px">وضعیت</x-admin.list.th>

            <x-admin.list.th>دسته‌بندی‌ها</x-admin.list.th>

            <x-admin.list.th>برچسب‌ها</x-admin.list.th>

            <x-admin.list.th>دیدگاه‌ها</x-admin.list.th>

            <x-admin.list.th>بازدید‌ها</x-admin.list.th>

            <x-admin.list.th>امتیاز سئو</x-admin.list.th>

            <x-admin.list.th
                width="130px"
                label="زمان انتشار"
                sortable
                sortKey="published_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @if($products->isNotEmpty())
            @foreach($products as $product)
                @php
                    $storeTitle = data_get($product->store, 'title')
                        ?? data_get($product->store, 'name')
                        ?? ('#' . $product->store_id);

                    $salePrices = $product->saleItems
                        ->pluck('base_price')
                        ->filter(fn($price) => filled($price))
                        ->map(fn($price) => (int) $price)
                        ->values();

                    $minPrice = $salePrices->min();
                    $maxPrice = $salePrices->max();
                    $itemUrl = $product->isPubliclyVisible()
                        ? $product->url
                        : route('preview.products', $product->id);
                    $itemUrlLabel = $product->isPubliclyVisible() ? 'مشاهده' : 'پیش‌نمایش';
                @endphp

                <x-admin.list.tr
                    ::class="{
                        'bg-slate-50/90': isSelected({{ $product->id }})
                    }"
                >
                    <x-admin.list.td align="center">
                        <x-admin.list.bulk-checkbox
                            name="ids[]"
                            :value="$product->id"
                            x-bind:checked="isSelected({{ $product->id }})"
                            x-on:change="toggleSelection({{ $product->id }})"
                        />
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.list.thumbnail
                            :src="$product->picture?->url"
                            :alt="$product->title"
                            size="md"
                        />
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[280px]">
                            <a
                                href="{{ $itemUrl }}"
                                target="_blank"
                                class="inline-block leading-6 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                            >
                                {{ $product->title }}
                            </a>

                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span>#{{ $product->id }}</span>

                                @if($product->slug)
                                    <span class="text-slate-300 pb-1">•</span>
                                    <span dir="ltr">{{ $product->slug }}</span>
                                @endif
                            </div>

                            @if($product->isEditLocked())
                                <p class="mt-2 text-xs font-bold text-amber-700">
                                    در حال ویرایش توسط {{ $product->editLockUser?->display_name ?: 'مدیر دیگر' }}
                                </p>
                            @endif

                            <x-admin.list.row-actions>
                                <x-admin.list.action
                                    tag="a"
                                    href="{{ route('admin.products.edit', $product) }}"
                                    variant="primary"
                                >
                                    ویرایش
                                </x-admin.list.action>

                                @can('analytics.view')
                                    <span class="text-slate-300">|</span>

                                    <x-admin.list.action
                                        tag="a"
                                        href="{{ route('admin.products.analytics', $product) }}"
                                    >
                                        گزارش فروش
                                    </x-admin.list.action>
                                @endcan

                                <span class="text-slate-300">|</span>

                                <x-admin.list.action
                                    tag="a"
                                    href="{{ $itemUrl }}"
                                    target="_blank"
                                >
                                    {{ $itemUrlLabel }}
                                </x-admin.list.action>

                                <span class="text-slate-300">|</span>

                                <x-admin.form
                                    action="{{ route('admin.products.destroy', $product) }}"
                                    method="delete"
                                    class="js-confirm"
                                    data-confirm-title="حذف محصول"
                                    data-confirm-text="آیا از حذف محصول «{{ $product->title }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست."
                                >
                                    <x-admin.list.action
                                        tag="button"
                                        type="submit"
                                        variant="danger"
                                    >
                                        حذف
                                    </x-admin.list.action>
                                </x-admin.form>
                            </x-admin.list.row-actions>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        @if($product->sku)
                            <x-admin.badge class="whitespace-nowrap" color="slate" size="md" dir="ltr">
                                {{ $product->sku }}
                            </x-admin.badge>
                        @else
                            <span class="text-xs text-slate-400">---</span>
                        @endif
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge
                            color="slate"
                            size="md"
                            class="whitespace-nowrap"
                        >
                            <a href="{{ route('stores.detail', $product->store) }}" target="_blank">
                                {{ $storeTitle }}
                            </a>
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        @if($product->saleItems->isEmpty())
                            <span class="text-xs text-slate-400 whitespace-nowrap">بدون آیتم فروش</span>
                        @elseif($salePrices->isEmpty())
                            <x-admin.badge class="whitespace-nowrap" color="slate" size="md">
                                رایگان
                            </x-admin.badge>
                        @elseif($minPrice === $maxPrice)
                            <x-admin.badge class="whitespace-nowrap" color="green" size="md">
                                {{ number_format($minPrice) }} تومان
                            </x-admin.badge>
                        @else
                            <div class="space-y-1 text-xs leading-5 text-slate-600">
                                <div class="whitespace-nowrap">از {{ number_format($minPrice) }} تومان</div>
                                <div class="whitespace-nowrap">تا {{ number_format($maxPrice) }} تومان</div>
                            </div>
                        @endif
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge color="blue" size="md">
                            {{ $product->type?->label() ?? match($product->type?->value) {
                                'digital' => 'دیجیتال',
                                'physical' => 'فیزیکی',
                                default => 'نامشخص',
                            } }}
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge color="slate" size="md">
                            {{ match($product->structure?->value) {
                                'simple' => 'ساده',
                                'variable' => 'متغیر',
                                default => 'نامشخص',
                            } }}
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        @php
                            $statusColor = match($product->status) {
                                \App\Domain\Catalog\Enums\ProductStatusEnum::ACTIVE => 'green',
                                \App\Domain\Catalog\Enums\ProductStatusEnum::UNDER_REVIEW => 'yellow',
                                \App\Domain\Catalog\Enums\ProductStatusEnum::INACTIVE => 'red',
                                default => 'slate',
                            };
                        @endphp

                        <x-admin.badge
                            :color="$statusColor"
                            size="md"
                        >
                            {{ $product->status?->label() ?? 'نامشخص' }}
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="flex flex-wrap gap-2">
                            @forelse($product->categories as $category)
                                <a
                                    href="{{ route('admin.products.index', ['category_ids' => [$category->id]]) }}"
                                    title="نمایش محصولات دسته «{{ $category->title }}»"
                                    class="rounded-full transition hover:opacity-75 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                >
                                    <x-admin.badge size="md" class="cursor-pointer whitespace-nowrap">
                                        {{ $category->title }}
                                    </x-admin.badge>
                                </a>
                            @empty
                                <span class="text-xs text-slate-400">---</span>
                            @endforelse
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="flex flex-wrap gap-2">
                            @forelse($product->tags as $tag)
                                <a
                                    href="{{ route('admin.products.index', ['tag_ids' => [$tag->id]]) }}"
                                    title="نمایش محصولات برچسب «{{ $tag->title }}»"
                                    class="rounded-full transition hover:opacity-75 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                >
                                    <x-admin.badge size="md" class="cursor-pointer whitespace-nowrap">
                                        {{ $tag->title }}
                                    </x-admin.badge>
                                </a>
                            @empty
                                <span class="text-xs text-slate-400">---</span>
                            @endforelse
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <a href="{{ route('admin.comments.index') . '?product_id=' . $product->id }}">
                            <x-admin.badge size="md">{{ $product->comments_count }}</x-admin.badge>
                        </a>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge size="md">{{ page_views($product) }}</x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.seo.score-indicator :score="$product->seoAnalysis?->score"/>
                    </x-admin.list.td>

                    <x-admin.list.td muted>
                        <div class="text-xs leading-6">
                            <div>{{ jalali_date($product->published_at, 'Y/m/d') }}</div>
                            <div>{{ jalali_date($product->published_at, 'H:i') }}</div>
                        </div>
                    </x-admin.list.td>
                </x-admin.list.tr>
            @endforeach
        @else
            <x-admin.list.empty
                title="محصولی برای نمایش وجود ندارد"
                description="هنوز هیچ محصولی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                action-text="افزودن محصول جدید"
                :action-href="route('admin.products.create')"
            />
        @endif
    </x-admin.list.tbody>
</x-admin.list.table>
