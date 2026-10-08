@php
    use App\Application\Media\Services\MediaImagePresenter;
    use App\Domain\Media\Enums\MediaFileStatusEnum;
    use App\Domain\Media\Enums\MediaFileTypeEnum;
    use App\Domain\Media\Enums\MediaFileVariantEnum;
    use App\Domain\Media\Enums\MediaFileVisibilityEnum;

    $typeLabel = fn ($type) => match ($type) {
        MediaFileTypeEnum::IMAGE => 'تصویر',
        MediaFileTypeEnum::VIDEO => 'ویدیو',
        MediaFileTypeEnum::AUDIO => 'صوت',
        MediaFileTypeEnum::DOCUMENT => 'سند',
        default => 'سایر',
    };

    $statusLabel = fn ($status) => match ($status) {
        MediaFileStatusEnum::READY => 'آماده',
        MediaFileStatusEnum::PROCESSING => 'در حال پردازش',
        MediaFileStatusEnum::FAILED => 'ناموفق',
        default => 'نامشخص',
    };

    $statusClass = fn ($status) => match ($status) {
        MediaFileStatusEnum::READY => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
        MediaFileStatusEnum::PROCESSING => 'bg-amber-50 text-amber-700 ring-amber-600/10',
        MediaFileStatusEnum::FAILED => 'bg-rose-50 text-rose-700 ring-rose-600/10',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };

    $variantLabel = fn ($variant) => match ($variant) {
        MediaFileVariantEnum::ORIGINAL => 'نسخه اصلی',
        MediaFileVariantEnum::THUMBNAIL => 'بندانگشتی',
        MediaFileVariantEnum::MEDIUM => 'متوسط',
        MediaFileVariantEnum::MEDIUM_LARGE => 'متوسط بزرگ',
        MediaFileVariantEnum::LARGE => 'بزرگ',
        default => 'نسخه فرعی',
    };

    $hasFilters = $search !== '' || $mediaType !== '' || $status !== '' || $visibility !== '' || $sort !== 'newest';
@endphp

<div
    class="space-y-5"
    x-data="{
        uploadOpen: false,
        uploading: false,
        uploadProgress: 0,
        toast: '',
        toastTimer: null,
        showToast(message) {
            this.toast = message;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => this.toast = '', 3500);
        },
        async copy(value, message = 'لینک در کلیپ‌بورد کپی شد.') {
            if (!value) return;
            try {
                await navigator.clipboard.writeText(value);
                this.showToast(message);
            } catch (error) {
                this.showToast('کپی خودکار انجام نشد.');
            }
        }
    }"
    x-on:livewire-upload-start="uploading = true; uploadProgress = 0"
    x-on:livewire-upload-progress="uploadProgress = $event.detail.progress"
    x-on:livewire-upload-finish="uploadProgress = 100"
    x-on:livewire-upload-error="uploading = false"
    x-on:media-library-uploaded.window="uploading = false; uploadProgress = 0; uploadOpen = $event.detail.failedCount > 0; showToast($event.detail.message)"
    x-on:media-library-upload-failed.window="uploading = false"
    x-on:media-library-notify.window="showToast($event.detail.message)"
>
    <section class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-orange-400 via-orange-500 to-amber-400"></div>

        <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600 ring-1 ring-orange-100">
                    <span class="[&_svg]:h-7 [&_svg]:w-7">@include('icons.image-03')</span>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-black text-slate-900 sm:text-2xl">کتابخانه رسانه</h1>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500">
                            {{ number_format($statistics['total']) }} رسانه
                        </span>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                        همه فایل‌های بارگذاری‌شده را یک‌جا ببینید، جست‌وجو کنید و جزئیات نسخه‌های آن‌ها را بررسی کنید.
                    </p>
                </div>
            </div>

            @can('media.create')
                <button
                    type="button"
                    @click="uploadOpen = !uploadOpen"
                    class="inline-flex h-11 shrink-0 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-orange-500 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-100"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 002 2h10a2 2 0 002-2v-4"/>
                    </svg>
                    افزودن رسانه
                </button>
            @endcan
        </div>
    </section>

    @can('media.create')
        <section
            x-show="uploadOpen"
            x-collapse
            x-cloak
            class="overflow-hidden rounded-[24px] border border-orange-200 bg-orange-50/50 shadow-sm"
        >
            <div class="flex items-center justify-between border-b border-orange-100 px-5 py-4">
                <div>
                    <h2 class="text-sm font-black text-slate-900">آپلود تصویر جدید</h2>
                    <p class="mt-1 text-xs text-slate-500">JPG، PNG یا WebP ـ حداکثر ۵ مگابایت برای هر فایل</p>
                </div>
                <button type="button" @click="uploadOpen = false" class="grid h-9 w-9 cursor-pointer place-items-center rounded-xl text-slate-400 transition hover:bg-white hover:text-slate-700" aria-label="بستن بخش آپلود">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <div class="p-5">
                <label class="group relative flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed border-orange-200 bg-white px-5 py-8 text-center transition hover:border-orange-400 hover:bg-orange-50/40">
                    <input type="file" wire:model="uploads" accept="image/jpeg,image/png,image/webp" multiple class="sr-only">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-50 text-orange-600 transition group-hover:scale-105">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 002 2h10a2 2 0 002-2v-4"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-sm font-black text-slate-800">تصاویر را انتخاب کنید</div>
                    <div class="mt-1 text-xs leading-6 text-slate-500">در هر نوبت می‌توانید تا ۲۰ تصویر را هم‌زمان بارگذاری کنید.</div>
                </label>

                <div x-show="uploading" x-cloak class="mt-4 rounded-2xl border border-orange-100 bg-white p-4">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-600">
                        <span>در حال انتقال فایل‌ها...</span>
                        <span x-text="uploadProgress + '%'" class="text-orange-600"></span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-orange-100">
                        <div class="h-full rounded-full bg-orange-500 transition-all" :style="`width: ${uploadProgress}%`"></div>
                    </div>
                </div>

                @if(count($uploads))
                    <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="text-sm font-black text-slate-800">{{ count($uploads) }} فایل آماده آپلود است</div>
                            <div class="mt-1 truncate text-xs text-slate-500">
                                {{ collect($uploads)->map(fn ($file) => $file->getClientOriginalName())->implode('، ') }}
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="saveUploads"
                            wire:loading.attr="disabled"
                            wire:target="saveUploads"
                            class="inline-flex h-10 shrink-0 cursor-pointer items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-bold text-white transition hover:bg-slate-800 disabled:cursor-wait disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="saveUploads">شروع آپلود</span>
                            <span wire:loading wire:target="saveUploads">در حال پردازش...</span>
                        </button>
                    </div>
                @endif

                @error('uploads') <p class="mt-3 text-xs font-bold text-rose-600">{{ $message }}</p> @enderror
                @error('uploads.*') <p class="mt-3 text-xs font-bold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </section>
    @endcan

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-500">کل رسانه‌ها</div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($statistics['total']) }}</div>
        </div>
        <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-500">تصاویر</div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($statistics['images']) }}</div>
        </div>
        <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-500">آماده استفاده</div>
            <div class="mt-2 text-2xl font-black text-emerald-600">{{ number_format($statistics['ready']) }}</div>
        </div>
        <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-500">حجم نسخه‌های اصلی</div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ \App\Livewire\Admin\Media\MediaLibrary::formatBytes($statistics['total_bytes']) }}</div>
        </div>
    </section>

    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="grid gap-3 xl:grid-cols-[minmax(260px,1fr)_repeat(4,minmax(140px,auto))_auto]">
            <label class="relative block">
                <span class="pointer-events-none absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-4-4"/></svg>
                </span>
                <input
                    type="search"
                    wire:model.live.debounce.450ms="search"
                    placeholder="جست‌وجو در عنوان، شناسه یا مسیر فایل..."
                    class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pr-11 pl-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-orange-300 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </label>

            <select wire:model.live="mediaType" class="h-11 cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-orange-300 focus:ring-4 focus:ring-orange-100">
                <option value="">همه نوع‌ها</option>
                @foreach($typeOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
            </select>

            <select wire:model.live="status" class="h-11 cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-orange-300 focus:ring-4 focus:ring-orange-100">
                <option value="">همه وضعیت‌ها</option>
                @foreach($statusOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
            </select>

            <select wire:model.live="visibility" class="h-11 cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-orange-300 focus:ring-4 focus:ring-orange-100">
                <option value="">همه دسترسی‌ها</option>
                <option value="public">عمومی</option>
                <option value="private">خصوصی</option>
            </select>

            <select wire:model.live="sort" class="h-11 cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-orange-300 focus:ring-4 focus:ring-orange-100">
                <option value="newest">جدیدترین</option>
                <option value="oldest">قدیمی‌ترین</option>
                <option value="largest">بیشترین حجم</option>
                <option value="title">عنوان</option>
            </select>

            <div class="flex h-11 items-center rounded-2xl border border-slate-200 bg-slate-50 p-1">
                <button type="button" wire:click="setViewMode('grid')" class="grid h-9 w-10 cursor-pointer place-items-center rounded-xl transition {{ $viewMode === 'grid' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-400 hover:text-slate-700' }}" title="نمای شبکه‌ای" aria-label="نمای شبکه‌ای">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg>
                </button>
                <button type="button" wire:click="setViewMode('list')" class="grid h-9 w-10 cursor-pointer place-items-center rounded-xl transition {{ $viewMode === 'list' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-400 hover:text-slate-700' }}" title="نمای فهرستی" aria-label="نمای فهرستی">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M9 6h11M9 12h11M9 18h11"/><circle cx="5" cy="6" r="1" fill="currentColor" stroke="none"/><circle cx="5" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="5" cy="18" r="1" fill="currentColor" stroke="none"/></svg>
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>{{ number_format($mediaFiles->total()) }} نتیجه</span>
                @if($hasFilters)
                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                    <button type="button" wire:click="resetFilters" class="cursor-pointer font-bold text-orange-600 hover:text-orange-700">پاک‌کردن فیلترها</button>
                @endif
            </div>
            <label class="flex items-center gap-2 text-xs text-slate-500">
                نمایش در صفحه
                <select wire:model.live="perPage" class="h-9 cursor-pointer rounded-xl border border-slate-200 bg-white px-2 text-xs font-bold text-slate-700 outline-none focus:border-orange-300">
                    @foreach([12, 24, 48, 96] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach
                </select>
            </label>
        </div>
    </section>

    <div class="relative min-h-40">
        <div wire:loading.flex wire:target="search,mediaType,status,visibility,sort,perPage,setViewMode,resetFilters,gotoPage,previousPage,nextPage" class="absolute inset-0 z-10 items-start justify-center rounded-[24px] bg-slate-100/70 pt-20 backdrop-blur-[2px]">
            <span class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-xs font-bold text-slate-600 shadow-lg ring-1 ring-slate-200">
                <svg class="h-4 w-4 animate-spin text-orange-500" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 00-9-9v3a6 6 0 016 6h3z"/></svg>
                در حال بروزرسانی...
            </span>
        </div>

        @if($mediaFiles->isEmpty())
            <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-6 py-20 text-center shadow-sm">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-slate-100 text-slate-400">
                    <span class="[&_svg]:h-8 [&_svg]:w-8">@include('icons.image-03')</span>
                </div>
                <h3 class="mt-5 text-base font-black text-slate-800">رسانه‌ای پیدا نشد</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
                    {{ $hasFilters ? 'عبارت جست‌وجو یا فیلترها را تغییر دهید.' : 'هنوز فایلی به کتابخانه رسانه اضافه نشده است.' }}
                </p>
                @if($hasFilters)
                    <button type="button" wire:click="resetFilters" class="mt-5 inline-flex h-10 cursor-pointer items-center rounded-2xl bg-slate-900 px-5 text-sm font-bold text-white hover:bg-slate-800">نمایش همه رسانه‌ها</button>
                @endif
            </div>
        @elseif($viewMode === 'grid')
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6">
                @foreach($mediaFiles as $media)
                    @php
                        $presenter = new MediaImagePresenter($media);
                        $previewUrl = $media->visibility === MediaFileVisibilityEnum::PUBLIC
                            ? $presenter->url(MediaFileVariantEnum::THUMBNAIL)
                            : null;
                        $primary = $media->primaryVariant;
                    @endphp
                    <button
                        type="button"
                        wire:key="media-grid-{{ $media->id }}"
                        wire:click="selectMedia({{ $media->id }})"
                        wire:loading.attr="disabled"
                        wire:target="selectMedia"
                        class="group cursor-pointer overflow-hidden rounded-[22px] border bg-white text-right shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-lg {{ $selectedMediaFileId === $media->id ? 'border-orange-400 ring-4 ring-orange-100' : 'border-slate-200' }}"
                    >
                        <div class="relative aspect-square overflow-hidden bg-[linear-gradient(45deg,#f8fafc_25%,transparent_25%),linear-gradient(-45deg,#f8fafc_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#f8fafc_75%),linear-gradient(-45deg,transparent_75%,#f8fafc_75%)] bg-[length:20px_20px] bg-[position:0_0,0_10px,10px_-10px,-10px_0px]">
                            @if($previewUrl && $media->type === MediaFileTypeEnum::IMAGE)
                                <img src="{{ $previewUrl }}" alt="{{ $media->title }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                            @else
                                <div class="grid h-full w-full place-items-center bg-slate-50 text-slate-300">
                                    <span class="[&_svg]:h-12 [&_svg]:w-12">@include('icons.image-03')</span>
                                </div>
                            @endif
                            <span class="absolute right-2 top-2 inline-flex rounded-full px-2 py-1 text-[10px] font-black ring-1 ring-inset backdrop-blur {{ $statusClass($media->status) }}">
                                {{ $statusLabel($media->status) }}
                            </span>
                            <span class="absolute bottom-2 left-2 rounded-lg bg-slate-950/70 px-2 py-1 text-[10px] font-bold text-white backdrop-blur">
                                #{{ $media->id }}
                            </span>
                        </div>
                        <div class="p-3.5">
                            <div class="truncate text-sm font-black text-slate-800" title="{{ $media->title }}">{{ $media->title ?: 'بدون عنوان' }}</div>
                            <div class="mt-2 flex items-center justify-between gap-2 text-[11px] text-slate-500">
                                <span>{{ $primary?->width && $primary?->height ? $primary->width.' × '.$primary->height : $typeLabel($media->type) }}</span>
                                <span>{{ \App\Livewire\Admin\Media\MediaLibrary::formatBytes($primary?->byte_size) }}</span>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
                <div class="hidden grid-cols-[72px_minmax(220px,1fr)_130px_130px_150px_100px] gap-4 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-black text-slate-500 lg:grid">
                    <span>پیش‌نمایش</span><span>رسانه</span><span>نوع و وضعیت</span><span>ابعاد و حجم</span><span>آپلودکننده</span><span>تاریخ</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($mediaFiles as $media)
                        @php
                            $presenter = new MediaImagePresenter($media);
                            $previewUrl = $media->visibility === MediaFileVisibilityEnum::PUBLIC
                                ? $presenter->url(MediaFileVariantEnum::THUMBNAIL)
                                : null;
                            $primary = $media->primaryVariant;
                        @endphp
                        <button type="button" wire:key="media-list-{{ $media->id }}" wire:click="selectMedia({{ $media->id }})" wire:loading.attr="disabled" wire:target="selectMedia" class="grid w-full cursor-pointer grid-cols-[64px_minmax(0,1fr)] gap-3 px-4 py-3.5 text-right transition hover:bg-orange-50/50 disabled:cursor-wait lg:grid-cols-[72px_minmax(220px,1fr)_130px_130px_150px_100px] lg:items-center lg:gap-4 {{ $selectedMediaFileId === $media->id ? 'bg-orange-50' : 'bg-white' }}">
                            <div class="h-16 w-16 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                @if($previewUrl && $media->type === MediaFileTypeEnum::IMAGE)
                                    <img src="{{ $previewUrl }}" alt="{{ $media->title }}" loading="lazy" class="h-full w-full object-cover">
                                @else
                                    <div class="grid h-full w-full place-items-center text-slate-300"><span class="[&_svg]:h-7 [&_svg]:w-7">@include('icons.image-03')</span></div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-black text-slate-800">{{ $media->title ?: 'بدون عنوان' }}</div>
                                <div class="mt-1 truncate text-xs text-slate-400 direction-ltr text-left">{{ $primary?->path ?: '—' }}</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-slate-500 lg:hidden">
                                    <span>#{{ $media->id }}</span><span>•</span><span>{{ $typeLabel($media->type) }}</span><span>•</span><span>{{ \App\Livewire\Admin\Media\MediaLibrary::formatBytes($primary?->byte_size) }}</span>
                                </div>
                            </div>
                            <div class="hidden lg:block">
                                <div class="text-xs font-bold text-slate-700">{{ $typeLabel($media->type) }}</div>
                                <span class="mt-1.5 inline-flex rounded-full px-2 py-1 text-[10px] font-black ring-1 ring-inset {{ $statusClass($media->status) }}">{{ $statusLabel($media->status) }}</span>
                            </div>
                            <div class="hidden text-xs leading-6 text-slate-600 lg:block">
                                <div>{{ $primary?->width && $primary?->height ? $primary->width.' × '.$primary->height : '—' }}</div>
                                <div class="text-slate-400">{{ \App\Livewire\Admin\Media\MediaLibrary::formatBytes($primary?->byte_size) }}</div>
                            </div>
                            <div class="hidden min-w-0 text-xs text-slate-600 lg:block">
                                <div class="truncate font-bold">{{ $media->creator?->display_name ?: trim(($media->creator?->first_name ?? '').' '.($media->creator?->last_name ?? '')) ?: 'نامشخص' }}</div>
                            </div>
                            <div class="hidden text-xs leading-6 text-slate-500 lg:block">{{ jalali_date($media->processed_at ?? $media->created_at, 'Y/n/j') }}</div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($mediaFiles->hasPages())
        <div class="rounded-[22px] border border-slate-200 bg-white px-4 py-4 shadow-sm">
            {{ $mediaFiles->links() }}
            <div class="mt-3 text-center text-xs text-slate-500">
                نمایش <span class="font-bold text-slate-700">{{ $mediaFiles->firstItem() }}</span> تا
                <span class="font-bold text-slate-700">{{ $mediaFiles->lastItem() }}</span> از
                <span class="font-bold text-slate-700">{{ number_format($mediaFiles->total()) }}</span> مورد
            </div>
        </div>
    @endif

    {{-- This drawer is already in the DOM, so it appears immediately while Livewire fetches the full details. --}}
    <div
        wire:loading.flex
        wire:target="selectMedia"
        class="fixed inset-x-0 top-0 h-[100dvh] justify-end overflow-hidden"
        style="z-index: 2147483000"
        role="status"
        aria-live="polite"
        aria-label="در حال دریافت جزئیات رسانه"
    >
        <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-[3px]"></div>

        <aside class="relative flex h-[100dvh] w-full max-w-[600px] flex-col overflow-hidden border-r border-white/10 bg-slate-50 shadow-2xl">
            <header class="flex h-[76px] shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="grid h-11 w-11 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 00-9-9v3a6 6 0 016 6h3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 sm:text-base">در حال دریافت جزئیات</h2>
                        <p class="mt-1 text-xs text-slate-500">اطلاعات رسانه تا چند لحظه دیگر آماده است</p>
                    </div>
                </div>
                <div class="h-10 w-10 rounded-2xl border border-slate-200 bg-slate-50"></div>
            </header>

            <div class="flex-1 overflow-hidden p-4 sm:p-6">
                <div class="animate-pulse space-y-4">
                    <div class="rounded-[26px] border border-slate-200 bg-white p-3 shadow-sm">
                        <div class="grid aspect-[16/10] place-items-center rounded-[20px] bg-slate-100">
                            <div class="grid h-16 w-16 place-items-center rounded-3xl bg-white text-slate-300 shadow-sm">
                                <span class="[&_svg]:h-8 [&_svg]:w-8">@include('icons.image-03')</span>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="h-3 w-20 rounded-full bg-slate-200"></div>
                        <div class="mt-4 h-11 rounded-2xl bg-slate-100"></div>
                    </div>
                    <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="h-4 w-28 rounded-full bg-slate-200"></div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="h-14 rounded-2xl bg-slate-100"></div>
                            <div class="h-14 rounded-2xl bg-slate-100"></div>
                            <div class="h-14 rounded-2xl bg-slate-100"></div>
                            <div class="h-14 rounded-2xl bg-slate-100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    @if($selectedMedia)
        @php
            $selectedPresenter = new MediaImagePresenter($selectedMedia);
            $selectedPrimary = $selectedMedia->primaryVariant;
            $selectedUrl = $selectedMedia->visibility === MediaFileVisibilityEnum::PUBLIC
                ? $selectedPresenter->url(MediaFileVariantEnum::ORIGINAL)
                : null;
            $selectedPreviewUrl = $selectedMedia->visibility === MediaFileVisibilityEnum::PUBLIC
                ? $selectedPresenter->url(MediaFileVariantEnum::MEDIUM)
                : null;
        @endphp
        <div
            class="fixed inset-x-0 top-0 flex h-[100dvh] justify-end overflow-hidden"
            style="z-index: 2147483000"
            role="dialog"
            aria-modal="true"
            aria-label="جزئیات رسانه"
            x-data="{
                visible: false,
                close() {
                    if (!this.visible) return;

                    this.visible = false;
                    setTimeout(() => this.$wire.closeDetails(), 240);
                }
            }"
            x-init="$nextTick(() => visible = true)"
            x-on:keydown.escape.window="close()"
        >
            <button
                type="button"
                x-show="visible"
                x-on:click="close()"
                class="absolute inset-0 cursor-default bg-slate-950/55 backdrop-blur-[3px]"
                aria-label="بستن جزئیات"
                x-transition:enter="transition-opacity duration-200 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-200 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            ></button>

            <aside
                x-show="visible"
                class="relative flex h-[100dvh] w-full max-w-[600px] flex-col overflow-hidden border-r border-white/10 bg-slate-50 shadow-2xl"
                x-transition:enter="transition-transform duration-300 ease-out"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform duration-200 ease-in"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
            >
                <header class="relative flex min-h-[84px] shrink-0 items-center justify-between overflow-hidden border-b border-orange-100 bg-white px-5 py-4 sm:px-6">
                    <div class="absolute inset-y-0 right-0 w-1 bg-gradient-to-b from-orange-400 to-orange-600"></div>
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600 ring-1 ring-orange-100">
                            <span class="[&_svg]:h-5 [&_svg]:w-5">@include('icons.image-03')</span>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-black text-slate-900">جزئیات رسانه</h2>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-black ring-1 ring-inset {{ $statusClass($selectedMedia->status) }}">{{ $statusLabel($selectedMedia->status) }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">شناسه رسانه #{{ $selectedMedia->id }}</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="close()" class="grid h-10 w-10 shrink-0 cursor-pointer place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900" aria-label="بستن">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6">
                    <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white p-3 shadow-sm">
                        <div class="relative grid min-h-64 place-items-center overflow-hidden rounded-[20px] bg-[linear-gradient(45deg,#f1f5f9_25%,transparent_25%),linear-gradient(-45deg,#f1f5f9_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#f1f5f9_75%),linear-gradient(-45deg,transparent_75%,#f1f5f9_75%)] bg-[length:24px_24px] bg-[position:0_0,0_12px,12px_-12px,-12px_0px]">
                            @if($selectedPreviewUrl && $selectedMedia->type === MediaFileTypeEnum::IMAGE)
                                <img src="{{ $selectedPreviewUrl }}" alt="{{ $selectedMedia->title }}" class="max-h-[380px] w-full object-contain">
                            @else
                                <div class="py-20 text-center text-slate-400">
                                    <span class="mx-auto block [&_svg]:h-14 [&_svg]:w-14">@include('icons.image-03')</span>
                                    <div class="mt-3 text-xs font-bold">پیش‌نمایش در دسترس نیست</div>
                                </div>
                            @endif
                            <div class="pointer-events-none absolute inset-x-3 bottom-3 flex items-center justify-between gap-2">
                                <span class="rounded-xl bg-slate-950/75 px-2.5 py-1.5 text-[10px] font-bold text-white backdrop-blur">{{ $typeLabel($selectedMedia->type) }}</span>
                                @if($selectedPrimary?->width && $selectedPrimary?->height)
                                    <span class="rounded-xl bg-slate-950/75 px-2.5 py-1.5 text-[10px] font-bold text-white backdrop-blur" dir="ltr">{{ $selectedPrimary->width }} × {{ $selectedPrimary->height }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                        <label class="text-xs font-black text-slate-600">عنوان رسانه</label>
                        @can('media.edit')
                            <div class="mt-2 flex gap-2">
                                <input type="text" wire:model="titleDraft" wire:keydown.enter="saveTitle" class="h-11 min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-orange-300 focus:ring-4 focus:ring-orange-100">
                                <button type="button" wire:click="saveTitle" wire:loading.attr="disabled" wire:target="saveTitle" class="h-11 shrink-0 cursor-pointer rounded-2xl bg-orange-500 px-4 text-sm font-bold text-white transition hover:bg-orange-600 disabled:opacity-60">ذخیره</button>
                            </div>
                            @error('titleDraft') <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p> @enderror
                        @else
                            <div class="mt-2 text-sm font-bold text-slate-800">{{ $selectedMedia->title ?: 'بدون عنوان' }}</div>
                        @endcan
                    </div>

                    <div class="mt-4 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-black text-slate-900">مشخصات فایل</h3>
                            <span class="text-[11px] font-bold text-slate-400">اطلاعات نسخه اصلی</span>
                        </div>
                        <dl class="mt-4 divide-y divide-slate-100 text-xs">
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="text-slate-500">نوع رسانه</dt><dd class="font-bold text-slate-800">{{ $typeLabel($selectedMedia->type) }}</dd></div>
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="text-slate-500">فرمت</dt><dd class="direction-ltr font-bold text-slate-800">{{ strtoupper($selectedPrimary?->format?->value ?? pathinfo($selectedPrimary?->path ?? '', PATHINFO_EXTENSION) ?: '—') }}</dd></div>
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="text-slate-500">ابعاد نسخه اصلی</dt><dd class="font-bold text-slate-800">{{ $selectedPrimary?->width && $selectedPrimary?->height ? $selectedPrimary->width.' × '.$selectedPrimary->height.' پیکسل' : '—' }}</dd></div>
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="text-slate-500">حجم نسخه اصلی</dt><dd class="font-bold text-slate-800">{{ \App\Livewire\Admin\Media\MediaLibrary::formatBytes($selectedPrimary?->byte_size) }}</dd></div>
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="text-slate-500">تعداد نسخه‌ها</dt><dd class="font-bold text-slate-800">{{ number_format($selectedMedia->variants->count()) }}</dd></div>
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="text-slate-500">سطح دسترسی</dt><dd class="font-bold text-slate-800">{{ $selectedMedia->visibility === MediaFileVisibilityEnum::PUBLIC ? 'عمومی' : 'خصوصی' }}</dd></div>
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="text-slate-500">آپلودکننده</dt><dd class="max-w-[65%] truncate font-bold text-slate-800">{{ $selectedMedia->creator?->display_name ?: trim(($selectedMedia->creator?->first_name ?? '').' '.($selectedMedia->creator?->last_name ?? '')) ?: 'نامشخص' }}</dd></div>
                            <div class="flex items-center justify-between gap-4 py-2.5"><dt class="text-slate-500">تاریخ آپلود</dt><dd class="font-bold text-slate-800">{{ jalali_date($selectedMedia->processed_at ?? $selectedMedia->created_at, 'Y/n/j - H:i') }}</dd></div>
                        </dl>
                    </div>

                    <div class="mt-4 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 class="text-sm font-black text-slate-900">مسیر و لینک</h3>
                        <div class="mt-3 rounded-2xl bg-slate-50 p-3">
                            <div class="text-[11px] font-bold text-slate-500">مسیر ذخیره‌سازی</div>
                            <div class="mt-1 break-all text-left font-mono text-[11px] leading-5 text-slate-700" dir="ltr">{{ $selectedPrimary?->path ?: '—' }}</div>
                        </div>
                        @if($selectedUrl)
                            <div class="mt-2 flex gap-2">
                                <button type="button" @click="copy(@js($selectedUrl))" class="inline-flex h-10 flex-1 cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V6a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h2"/></svg>
                                    کپی لینک
                                </button>
                                <a href="{{ $selectedUrl }}" target="_blank" rel="noopener" class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-2xl bg-slate-900 text-xs font-bold text-white transition hover:bg-slate-800">
                                    نمایش فایل اصلی
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5M19 5l-8 8M19 14v3a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h3"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($selectedMedia->variants->isNotEmpty())
                        <div class="mt-4 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                            <h3 class="text-sm font-black text-slate-900">نسخه‌های تولیدشده</h3>
                            <div class="mt-3 space-y-2">
                                @foreach($selectedMedia->variants as $variant)
                                    @php
                                        $variantUrl = $selectedMedia->visibility === MediaFileVisibilityEnum::PUBLIC
                                            ? \Illuminate\Support\Facades\Storage::disk($variant->disk->value)->url($variant->path)
                                            : null;
                                    @endphp
                                    <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-black text-slate-700">{{ $variantLabel($variant->variant) }}</div>
                                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[10px] text-slate-500">
                                                <span>{{ $variant->width && $variant->height ? $variant->width.' × '.$variant->height : 'ابعاد نامشخص' }}</span>
                                                <span>•</span>
                                                <span>{{ \App\Livewire\Admin\Media\MediaLibrary::formatBytes($variant->byte_size) }}</span>
                                                <span>•</span>
                                                <span class="direction-ltr">{{ strtoupper($variant->format->value) }}</span>
                                            </div>
                                        </div>
                                        @if($variantUrl)
                                            <button type="button" @click="copy(@js($variantUrl), 'لینک این نسخه کپی شد.')" class="grid h-9 w-9 shrink-0 cursor-pointer place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-orange-200 hover:text-orange-600" title="کپی لینک" aria-label="کپی لینک {{ $variantLabel($variant->variant) }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V6a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h2"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    @endif

    <div x-show="toast" x-cloak x-transition.opacity class="pointer-events-none fixed bottom-5 left-5 z-[90] max-w-sm rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-2xl" x-text="toast"></div>
</div>
