@extends('admin.layout')

@section('title', 'رسانه‌های ارسالی کاربران')

@section('content')
    <x-admin.page>
        <div class="space-y-6">
            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            <x-admin.metabox title="تنظیمات برگه گالری">
                <div class="mb-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs font-bold leading-6 text-sky-800 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                    ارسال‌های کاربران دقیقاً در همین صفحه (زیر همین متاباکس) بررسی می‌شوند: با «تأیید» رسانه در برگه عمومی منتشر و امتیازش خودکار به حساب ارسال‌کننده واریز می‌شود.
                </div>

                <x-admin.form action="{{ route('admin.gallery.settings.store') }}" method="put">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field label="عنوان برگه">
                            <x-admin.form.input
                                name="page_title"
                                value="{{ old('page_title', get_setting('gallery', 'page_title', 'رسانه‌های ارسالی کاربران')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="زیرعنوان برگه" class="lg:col-span-2">
                            <x-admin.form.input
                                name="page_subtitle"
                                value="{{ old('page_subtitle', get_setting('gallery', 'page_subtitle', 'گیم‌پلی‌ها و تصاویری که کاربران مودینگ‌وی فرستاده‌اند')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="تعداد در هر صفحه">
                            <x-admin.form.input
                                name="per_page"
                                type="number"
                                value="{{ old('per_page', get_setting('gallery', 'per_page', 9)) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="امتیاز تصویر تأییدشده">
                            <x-admin.form.input
                                name="image_points"
                                type="number"
                                value="{{ old('image_points', get_setting('gallery', 'image_points', 150)) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="امتیاز ویدیوی تأییدشده">
                            <x-admin.form.input
                                name="video_points"
                                type="number"
                                value="{{ old('video_points', get_setting('gallery', 'video_points', 500)) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="امکان ارسال توسط کاربر" class="switch-field">
                            <x-admin.form.switch
                                name="submit_enabled"
                                :checked="old('submit_enabled', get_setting('gallery', 'submit_enabled', 1))"
                            />
                        </x-admin.field>
                    </div>

                    <x-admin.form.save-button label="ذخیره تنظیمات برگه"/>
                </x-admin.form>
            </x-admin.metabox>

            <x-admin.metabox title="رسانه‌های ارسالی">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.gallery.index') }}"
                       class="inline-flex h-9 items-center rounded-xl border px-4 text-xs font-extrabold transition {{ is_null($status) ? 'border-brand-500 bg-brand-500 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        همه ({{ number_format($counts['all']) }})
                    </a>

                    @foreach(['pending' => 'در انتظار تأیید', 'approved' => 'تأییدشده', 'rejected' => 'ردشده'] as $key => $label)
                        <a href="{{ route('admin.gallery.index', ['status' => $key]) }}"
                           class="inline-flex h-9 items-center rounded-xl border px-4 text-xs font-extrabold transition {{ $status === $key ? 'border-brand-500 bg-brand-500 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                            {{ $label }} ({{ number_format($counts[$key]) }})
                        </a>
                    @endforeach
                </div>

                @if($submissions->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                            <tr class="text-right text-xs text-slate-500 dark:text-slate-400">
                                <th class="p-3 font-extrabold">رسانه</th>
                                <th class="p-3 font-extrabold">عنوان</th>
                                <th class="p-3 font-extrabold">ارسال‌کننده</th>
                                <th class="p-3 font-extrabold">تاریخ</th>
                                <th class="p-3 font-extrabold">وضعیت</th>
                                <th class="p-3 font-extrabold">اقدام</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($submissions as $submission)
                                <tr class="border-t border-slate-100 dark:border-slate-800">
                                    <td class="p-3">
                                        @php($adminThumb = $submission->image_url ?: ($submission->image_path ? asset($submission->image_path) : null))
                                        @if($submission->type->value === 'image' && $adminThumb)
                                            <img src="{{ $adminThumb }}" alt="{{ $submission->title }}"
                                                 class="h-14 w-20 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                                        @else
                                            <span class="grid h-14 w-20 place-items-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800 [&_svg]:h-5 [&_svg]:w-5">
                                                @include('icons.play')
                                            </span>
                                        @endif
                                    </td>

                                    <td class="max-w-[220px] p-3">
                                        <div class="truncate font-bold dark:text-slate-100">{{ $submission->title }}</div>
                                        <div class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $submission->description }}</div>
                                        @if($submission->video_url)
                                            <a href="{{ $submission->video_url }}" target="_blank" rel="noopener"
                                               class="mt-1 inline-block text-[11px] font-bold text-brand-600 hover:underline dark:text-brand-400">مشاهده ویدیو ↗</a>
                                        @endif
                                    </td>

                                    <td class="p-3">
                                        <div class="font-bold dark:text-slate-100">{{ $submission->submitterName() }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{{ $submission->user?->mobile }}</div>
                                    </td>

                                    <td class="p-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ jalali_date($submission->created_at, 'Y/m/d H:i') }}
                                    </td>

                                    <td class="p-3">
                                        <span class="rounded-lg px-2.5 py-1 text-[11px] font-extrabold
                                            {{ $submission->status->value === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($submission->status->value === 'rejected' ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300') }}">
                                            {{ $submission->status->label() }}
                                        </span>
                                    </td>

                                    <td class="p-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if($submission->status->value !== 'approved')
                                                <form action="{{ route('admin.gallery.status', $submission) }}" method="post">
                                                    @csrf
                                                    @method('patch')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit"
                                                            class="inline-flex h-9 cursor-pointer items-center rounded-xl bg-emerald-500 px-3 text-xs font-extrabold text-white transition hover:bg-emerald-600">
                                                        تأیید
                                                    </button>
                                                </form>
                                            @endif

                                            @if($submission->status->value !== 'rejected')
                                                <form action="{{ route('admin.gallery.status', $submission) }}" method="post">
                                                    @csrf
                                                    @method('patch')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit"
                                                            class="inline-flex h-9 cursor-pointer items-center rounded-xl border border-red-200 px-3 text-xs font-extrabold text-red-600 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10">
                                                        رد
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.gallery.destroy', $submission) }}" method="post"
                                                  class="confirmationForm js-confirm"
                                                  data-confirm-title="حذف رسانه"
                                                  data-confirm-text="این رسانه برای همیشه حذف شود؟">
                                                @csrf
                                                @method('delete')
                                                <button type="submit"
                                                        class="inline-flex h-9 cursor-pointer items-center rounded-xl border border-slate-200 px-3 text-xs font-extrabold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($submissions->hasPages())
                        <div class="mt-4">
                            {{ $submissions->links() }}
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 p-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        رسانه‌ای در این وضعیت وجود ندارد.
                    </div>
                @endif
            </x-admin.metabox>
        </div>
    </x-admin.page>
@endsection
