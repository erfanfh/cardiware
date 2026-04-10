@extends('front.master')

@section('content')
    <section class="relative overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-b from-white via-slate-50/70 to-white"></div>

        <!-- Decorative blobs -->
        <div
            class="pointer-events-none absolute -top-20 -right-16 h-56 w-56 rounded-full bg-brand-100/50 blur-3xl"></div>
        <div
            class="pointer-events-none absolute -bottom-20 -left-16 h-56 w-56 rounded-full bg-orange-100/40 blur-3xl"></div>

        <div class="relative mx-auto max-w-4xl px-8 py-16 sm:px-6 sm:py-10 lg:px-8">
            <div class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur sm:p-10">
                <!-- Top badge -->
                <div
                    class="mx-auto inline-flex items-center gap-2 rounded-full border border-brand-100 bg-brand-50 px-4 py-2 text-sm font-bold text-brand-700">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-brand-500"></span>
                    در حال توسعه
                </div>

                <!-- Title -->
                <div class="mt-8 text-center">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        صفحه حساب کاربری به‌زودی فعال می‌شود
                    </h1>

                    <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                        ما در حال آماده‌سازی بخش حساب کاربری مودینگ‌وی هستیم تا تجربه‌ای کامل‌تر،
                        مرتب‌تر و حرفه‌ای‌تر در اختیار شما قرار بگیرد.
                        این بخش به زودی در دسترس قرار می گیرد.
                    </p>
                </div>

                <!-- Small features preview -->
                <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                        <div class="text-sm font-extrabold text-slate-900">مدیریت اطلاعات کاربری</div>
                        <div class="mt-1 text-xs leading-6 text-slate-500">
                            مشاهده و ویرایش اطلاعات حساب
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                        <div class="text-sm font-extrabold text-slate-900">پیگیری فعالیت‌ها</div>
                        <div class="mt-1 text-xs leading-6 text-slate-500">
                            دسترسی سریع به وضعیت‌ها و تعاملات
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                        <div class="text-sm font-extrabold text-slate-900">امکانات بیشتر</div>
                        <div class="mt-1 text-xs leading-6 text-slate-500">
                            قابلیت‌های اختصاصی در نسخه‌های بعدی
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('home') }}"
                       class="inline-flex h-12 items-center justify-center rounded-2xl bg-brand-600 px-6 text-sm font-extrabold text-white transition hover:bg-brand-700">
                        بازگشت به صفحه اصلی
                    </a>

                    <a href="{{ route('posts.list') }}"
                       class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                        مشاهده مطالب سایت
                    </a>
                </div>

                <!-- Bottom note -->
                <div class="mt-8 border-t border-dashed border-slate-200 pt-5 text-center">
                    <p class="text-xs leading-6 text-slate-500 sm:text-sm">
                        از صبوری و همراهی شما ممنونیم. این بخش به‌زودی با ظاهری کامل و امکانات بیشتر منتشر خواهد شد.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
