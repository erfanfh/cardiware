@extends('admin.layout')

@section('title', 'داشبورد')

@section('content')
    <div class="space-y-6">
        <section
            class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white p-6 sm:p-7 lg:p-8 shadow-sm">
            <div
                class="absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-orange-50 via-orange-50/40 to-transparent pointer-events-none"></div>
            <div
                class="absolute -top-16 -left-16 h-40 w-40 rounded-full bg-orange-100/60 blur-3xl pointer-events-none"></div>
            <div
                class="absolute -bottom-16 left-24 h-36 w-36 rounded-full bg-amber-100/50 blur-3xl pointer-events-none"></div>

            <div class="relative flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-orange-100 bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">
                        <span class="inline-block h-2 w-2 rounded-full bg-orange-500"></span>
                        داشبورد مدیریت مودینگ وی
                    </div>

                    <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        خوش آمدی،
                        <span class="text-orange-600">{{ auth()->user()?->display_name ?? 'مدیر' }}</span>
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 text-justify sm:text-[15px]">
                        در این صفحه می‌توانید نمایی کامل از وضعیت کلی سایت، محتوای منتشرشده، فعالیت کاربران و بخش‌های
                        مهم مدیریتی را مشاهده کنید.
                        همچنین دسترسی سریع به قسمت‌های مختلف برای بررسی، مدیریت و تصمیم‌گیری روزانه در اختیار شما قرار
                        دارد.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        @can('post.create')
                            <a href="{{ route('admin.posts.create') }}"
                               class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-orange-500 px-5 text-sm font-bold text-white transition hover:bg-orange-600">
                                <span class="[&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.plus-sign')
                                </span>
                                نوشته جدید
                            </a>
                        @endcan

                        @can('project.create')
                            <a href="{{ route('admin.projects.create') }}"
                               class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                <span class="[&_svg]:h-5 [&_svg]:w-5 text-slate-500">
                                    @include('icons.plus-sign')
                                </span>
                                پروژه جدید
                            </a>
                        @endcan

                        @can('page.create')
                            <a href="{{ route('admin.pages.create') }}"
                               class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                <span class="[&_svg]:h-5 [&_svg]:w-5 text-slate-500">
                                    @include('icons.plus-sign')
                                </span>
                                برگه جدید
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:w-[340px]">
                    @can('product.index')
                        <a href="#"
                           class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-black text-slate-900">محصولات</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ number_format($stats['products']) }} منتشرشده
                                    </div>
                                </div>

                                <span
                                    class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.shopping-basket-add-01')
                                </span>
                            </div>
                        </a>
                    @endcan

                    @can('user.index')
                        <a href="{{ route('admin.users.index') }}"
                           class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-black text-slate-900">کاربران</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ number_format($stats['users'] ?? 0) }} کاربر
                                    </div>
                                </div>

                                <span
                                    class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                @include('icons.user-multiple')
                            </span>
                            </div>
                        </a>
                    @endcan

                    <a href="#"
                       class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-black text-slate-900">فروش امروز</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    نامشخص
                                </div>
                            </div>

                            <span
                                class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                @include('icons.chart-03')
                            </span>
                        </div>
                    </a>

                    <a href="#"
                       class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-black text-slate-900">فروش این ماه</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    نامشخص
                                </div>
                            </div>

                            <span
                                class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                @include('icons.chart-03')
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        {{-- Main Stats --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @can('post.index')
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-slate-500">کل نوشته‌ها</div>
                            <div
                                class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['posts']) }}</div>
                            <div class="mt-2 text-xs font-medium text-emerald-600">
                                {{ number_format($stats['month_posts']) }} این ماه
                            </div>
                        </div>
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                            <span class="[&_svg]:h-6 [&_svg]:w-6">
                                @include('icons.file-02')
                            </span>
                        </div>
                    </div>
                </div>
            @endcan

            @can('project.index')
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-slate-500">کل پروژه‌ها</div>
                            <div
                                class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['projects']) }}</div>
                            <div class="mt-2 text-xs font-medium text-sky-600">
                                {{ number_format($stats['active_projects']) }} پروژه فعال
                            </div>
                        </div>
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-sky-600">
                            <span class="[&_svg]:h-6 [&_svg]:w-6">
                                @include('icons.folder-management')
                            </span>
                        </div>
                    </div>
                </div>
            @endcan

            @can('post.index')
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-slate-500">دانلودها</div>
                            <div
                                class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['downloads']) }}</div>
                            <div class="mt-2 text-xs font-medium text-violet-600">
                                محبوب ترین:
                                {{ number_format($stats['popular_downloads']) }}
                            </div>
                        </div>
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-violet-50 text-violet-600">
                            <span class="[&_svg]:h-6 [&_svg]:w-6">
                                @include('icons.download-01')
                            </span>
                        </div>
                    </div>
                </div>
            @endcan

            @can('comment.index')
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-slate-500">دیدگاه‌ها</div>
                            <div
                                class="mt-2 text-3xl font-black text-slate-900">{{ number_format($stats['comments']) }}</div>
                            <div class="mt-2 text-xs font-medium text-rose-600">
                                {{ number_format($stats['pending_comments']) }} نیازمند بررسی
                            </div>
                        </div>
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-rose-50 text-rose-600">
                            <span class="[&_svg]:h-6 [&_svg]:w-6">
                                @include('icons.comment-01')
                            </span>
                        </div>
                    </div>
                </div>
            @endcan
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-8 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-900">دسترسی سریع به ماژول‌ها</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            مسیرهای پرتکرار مدیریت مودینگ‌وی برای کار روزانه
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @can('post.index')
                        <a href="{{ route('admin.posts.index') }}"
                           class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-black text-slate-900">نوشته‌ها</div>
                                    <div class="mt-1 text-xs leading-6 text-slate-500">
                                        مدیریت مطالب، دسته‌بندی، برچسب، انتشار و ویرایش
                                    </div>
                                </div>
                                <span
                                    class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.file-02')
                                </span>
                            </div>
                        </a>
                    @endcan

                    @can('page.index')
                        <a href="{{ route('admin.pages.index') }}"
                           class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-black text-slate-900">برگه‌ها</div>
                                    <div class="mt-1 text-xs leading-6 text-slate-500">
                                        مدیریت صفحات ثابت و بخش‌های محتوایی سایت
                                    </div>
                                </div>
                                <span
                                    class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.file-empty-02')
                                </span>
                            </div>
                        </a>
                    @endcan

                    @can('comment.index')
                        <a href="{{ route('admin.comments.index') }}"
                           class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-black text-slate-900">دیدگاه‌ها</div>
                                    <div class="mt-1 text-xs leading-6 text-slate-500">
                                        بررسی، تایید، پاسخ و مدیریت مکالمات کاربران
                                    </div>
                                </div>
                                <span
                                    class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.comment-01')
                                </span>
                            </div>
                        </a>
                    @endcan

                    @can('project.index')
                        <a href="{{ route('admin.projects.index') }}"
                           class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-black text-slate-900">پروژه‌ها</div>
                                    <div class="mt-1 text-xs leading-6 text-slate-500">
                                        مدیریت پروژه‌ها، وضعیت‌ها و صفحات معرفی
                                    </div>
                                </div>
                                <span
                                    class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.folder-management')
                                </span>
                            </div>
                        </a>
                    @endcan

                    @can('user.index')
                        <a href="{{ route('admin.users.index') }}"
                           class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-black text-slate-900">کاربران</div>
                                    <div class="mt-1 text-xs leading-6 text-slate-500">
                                        کاربران، نقش‌ها، سطوح دسترسی و مدیریت حساب‌ها
                                    </div>
                                </div>
                                <span
                                    class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.user-multiple')
                                </span>
                            </div>
                        </a>
                    @endcan

                    @can('setting.general')
                        <a href="{{ route('admin.settings.general') }}"
                           class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-black text-slate-900">تنظیمات سایت</div>
                                    <div class="mt-1 text-xs leading-6 text-slate-500">
                                        تنظیمات عمومی، صفحه اصلی، هدر، تبلیغات و ...
                                    </div>
                                </div>
                                <span
                                    class="text-slate-400 transition group-hover:text-orange-600 [&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.settings-02')
                                </span>
                            </div>
                        </a>
                    @endcan
                </div>
            </div>

            <div class="lg:col-span-4 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-900">نیازمند رسیدگی</h2>
                        <p class="mt-1 text-sm text-slate-500">چیزهایی که بهتره امروز بررسی بشن</p>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @can('comment.index')
                        <a href="{{ route('admin.comments.index') }}"
                           class="flex items-center justify-between rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 transition hover:bg-rose-100/70">
                            <div>
                                <div class="text-sm font-black text-slate-900">دیدگاه‌های در انتظار تایید</div>
                                <div class="mt-1 text-xs text-slate-500">بررسی تعامل کاربران و پاسخ سریع</div>
                            </div>
                            <div
                                class="text-lg font-black text-rose-600">{{ number_format($stats['pending_comments']) }}</div>
                        </a>
                    @endcan

                    @can('post.index')
                        <a href="{{ route('admin.posts.index', ['status' => 'inactive']) }}"
                           class="flex items-center justify-between rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 transition hover:bg-amber-100/70">
                            <div>
                                <div class="text-sm font-black text-slate-900">نوشته‌های پیش‌نویس</div>
                                <div class="mt-1 text-xs text-slate-500">مطالبی که هنوز منتشر نشده‌اند</div>
                            </div>
                            <div
                                class="text-lg font-black text-amber-600">{{ number_format($stats['inactive_posts']) }}</div>
                        </a>
                    @endcan

                    @can('product.index')
                        <a href="#"
                           class="flex items-center justify-between rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 transition hover:bg-sky-100/70">
                            <div>
                                <div class="text-sm font-black text-slate-900">محصولات پیش‌نویس</div>
                                <div class="mt-1 text-xs text-slate-500">محصولاتی که هنوز منتشر نشده‌اند</div>
                            </div>
                            <div
                                class="text-lg font-black text-sky-600">{{ number_format($stats['inactive_products']) }}</div>
                        </a>
                    @endcan
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            @can('post.index')
                <div class="xl:col-span-7 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-slate-900">پربازدیدترین های دیروز</h2>
                            <p class="mt-1 text-sm text-slate-500">نوشته های با بیشترین بازدید در دیروز</p>
                        </div>

                        <a href="{{ route('admin.posts.index') }}"
                           class="text-sm font-bold text-orange-600 transition hover:text-orange-700">
                            مشاهده همه
                        </a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse($topYesterdayPosts as $post)
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div
                                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white border border-slate-200 text-slate-500">
                            <span class="[&_svg]:h-5 [&_svg]:w-5">
                                @include('icons.file-02')
                            </span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-black text-slate-900">
                                        {{ $post->title }}
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                        <span>{{ $post->author?->display_name ?? '—' }}</span>
                                        <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                        <span>{{ jalali_date($post->published_at, 'Y/n/j - H:i') }}</span>
                                        <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 font-bold
                                    {{ $post->status === \App\Domain\Content\Enums\PostStatusEnum::ACTIVE ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $post->status === \App\Domain\Content\Enums\PostStatusEnum::ACTIVE ? 'منتشر شده' : 'غیرفعال' }}
                                </span>
                                    </div>
                                </div>

                                <a href="{{ route('admin.posts.edit', $post) }}"
                                   class="inline-flex h-10 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700 transition hover:bg-slate-100">
                                    ویرایش
                                </a>
                            </div>
                        @empty
                            <div
                                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                هنوز نوشته‌ای برای نمایش وجود ندارد.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endcan

            @can('comment.index')
                <div class="xl:col-span-5 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-black text-slate-900">آخرین دیدگاه‌ها</h2>
                                <p class="mt-1 text-sm text-slate-500">جدیدترین تعاملات کاربران</p>
                            </div>

                            <a href="{{ route('admin.comments.index') }}"
                               class="text-sm font-bold text-orange-600 transition hover:text-orange-700">
                                مدیریت دیدگاه‌ها
                            </a>
                        </div>

                        <div class="mt-5 space-y-3">
                            @forelse($recentComments as $comment)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <div class="text-sm font-black text-slate-900 truncate">
                                                    {{ $comment->user?->display_name ?? $comment->author_name }}
                                                </div>

                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold
                                        {{ $comment->isApproved() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $comment->isApproved() ? 'تایید شده' : 'در انتظار' }}
                                    </span>
                                            </div>

                                            <div class="mt-1 text-xs text-slate-500">
                                                برای:
                                                <span
                                                    class="font-bold text-slate-700">{{ $comment->commentable?->title ?? '—' }}</span>
                                            </div>
                                        </div>

                                        <div class="text-[11px] text-slate-400 whitespace-nowrap">
                                            {{ jalali_date($comment->created_at, 'Y/n/j') }}
                                        </div>
                                    </div>

                                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">
                                        {{ $comment->content }}
                                    </p>
                                </div>
                            @empty
                                <div
                                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                    هنوز دیدگاهی ثبت نشده.
                                </div>
                            @endforelse
                        </div>
                    </div>
            @endcan
        </section>
    </div>
@endsection
