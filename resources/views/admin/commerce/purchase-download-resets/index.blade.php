@extends('admin.layout')

@section('title', 'بازسازی محدودیت دانلود')

@section('content')
    <div class="mx-auto max-w-6xl space-y-5">
        <section class=" rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700 [&_svg]:h-5 [&_svg]:w-5">
                        @include('icons.arrow-reload-horizontal')
                    </div>

                    <div>
                        <h1 class="text-base font-black text-slate-900">بازسازی محدودیت دانلود کاربر</h1>
                        <p class="mt-1 text-sm leading-7 text-slate-500">
                            کاربر را انتخاب کنید، سپس دانلودهای محصول موردنظر را با یک کلیک صفر کنید.
                        </p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.purchase-download-resets.index') }}" class="p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-end">
                    <div class="md:col-span-9">
                        <x-admin.form.async-select
                                name="user_id"
                                label="کاربر"
                                placeholder="نام، موبایل یا ایمیل کاربر را جستجو کنید..."
                                :search-url="route('admin.purchase-download-resets.users.search')"
                                :selected-options="$selectedUserOption"
                                :value="request('user_id')"
                                :error="$errors->first('user_id')"
                                required
                        />
                    </div>

                    <div class="md:col-span-3">
                        <x-admin.form.button type="submit" variant="dark" size="lg" block>
                            نمایش خریدهای کاربر
                        </x-admin.form.button>
                    </div>
                </div>
            </form>
        </section>

        @if($selectedUser)
            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-500">کاربر انتخاب شده</div>
                        <div class="mt-1 truncate text-sm font-black text-slate-900">
                            {{ $selectedUser->display_name ?: $selectedUser->full_name }}
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs font-bold text-slate-600">
                        <span class="rounded-xl bg-slate-100 px-3 py-2" dir="ltr">
                            {{ $selectedUser->mobile }}
                        </span>

                        @if($selectedUser->email)
                            <span class="rounded-xl bg-slate-100 px-3 py-2" dir="ltr">
                                {{ $selectedUser->email }}
                            </span>
                        @endif
                    </div>
                </div>
            </section>

            @if($purchaseAccesses->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center shadow-sm">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500 [&_svg]:w-6 [&_svg]:h-6">
                        @include('icons.cloud-download')
                    </div>
                    <h2 class="mt-4 text-sm font-black text-slate-900">خرید دانلودی پیدا نشد</h2>
                    <p class="mt-2 text-sm text-slate-500">برای این کاربر هنوز دسترسی دانلود محصولی ثبت نشده است.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($purchaseAccesses as $access)
                        @php
                            $productTitle = $access->product?->title
                                ?? data_get($access->metadata, 'order_item_snapshot.title')
                                ?? 'محصول خریداری شده';

                            $saleItemTitle = $access->productSaleItem?->title
                                ?? data_get($access->metadata, 'order_item_snapshot.product_sale_item_title');

                            $orderNumber = data_get($access->metadata, 'order_number')
                                ?? $access->order_id;

                            $isExpired = $access->isExpiredByDate();
                            $statusLabel = $isExpired ? 'منقضی شده' : $access->status->label();
                            $statusClass = $access->isUsable()
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-amber-200 bg-amber-50 text-amber-700';

                            $files = $access->productSaleItem?->files ?? collect();
                            $downloadCountsByFile = $access->downloads
                                ->countBy('product_sale_item_file_id');
                        @endphp

                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-sm font-black text-slate-900">{{ $productTitle }}</h2>

                                        <span class="rounded-lg border px-2 py-1 text-[11px] font-extrabold {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-bold text-slate-500">
                                        @if($saleItemTitle)
                                            <span>{{ $saleItemTitle }}</span>
                                        @endif

                                        <span>سفارش: {{ $orderNumber }}</span>

                                        <span>
                                            سقف دانلود:
                                            {{ $access->download_limit === null ? 'نامحدود' : $access->download_limit . ' بار برای هر فایل' }}
                                        </span>

                                    </div>

                                    @if($files->isNotEmpty())
                                        <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                                            @foreach($files as $file)
                                                @php
                                                    $fileDownloadsCount = (int)$downloadCountsByFile
                                                        ->get($file->getKey(), 0);
                                                    $fileDownloadLimit = $access->download_limit;
                                                @endphp

                                                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-3 py-2.5 last:border-b-0">
                                                    <span class="min-w-0 truncate text-xs font-bold text-slate-700">
                                                        {{ $file->title ?: 'فایل شماره ' . $loop->iteration }}
                                                    </span>

                                                    <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-extrabold text-slate-600">
                                                        @if($fileDownloadLimit === null)
                                                            {{ $fileDownloadsCount }} بار دانلود
                                                        @else
                                                            {{ $fileDownloadsCount }} از {{ $fileDownloadLimit }} بار
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-4 rounded-xl border border-dashed border-slate-200 px-3 py-3 text-xs font-bold text-slate-500">
                                            فایلی برای این آیتم فروش ثبت نشده است.
                                        </div>
                                    @endif
                                </div>

                                <form
                                        method="POST"
                                        action="{{ route('admin.purchase-download-resets.reset', $access->getKey()) }}"
                                        class="shrink-0"
                                        onsubmit="return confirm('همه دانلودهای ثبت شده این محصول برای کاربر حذف و شمارنده صفر شود؟')"
                                >
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $selectedUser->getKey() }}">

                                    <x-admin.form.button
                                            type="submit"
                                            variant="danger"
                                            :disabled="$access->downloads_count < 1"
                                            class="[&_svg]:w-4 [&_svg]:h-4"
                                    >
                                        @include('icons.arrow-reload-horizontal')
                                        {{ $access->downloads_count > 0 ? 'بازسازی دانلودها' : 'دانلودی ثبت نشده' }}
                                    </x-admin.form.button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection
