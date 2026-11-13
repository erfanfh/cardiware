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
                                value="{{ old('image_points', get_setting('gallery', 'image_points', 80)) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="امتیاز ویدیوی تأییدشده">
                            <x-admin.form.input
                                name="video_points"
                                type="number"
                                value="{{ old('video_points', get_setting('gallery', 'video_points', 200)) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="درصد امتیاز دومین رسانه کاربر" hint="اولین رسانه‌ی تأییدشده‌ی هر کاربر امتیاز کامل می‌گیرد؛ دومی این درصد.">
                            <x-admin.form.input
                                name="second_points_percent"
                                type="number" min="0" max="100"
                                value="{{ old('second_points_percent', get_setting('gallery', 'second_points_percent', 50)) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="درصد امتیاز سومین رسانه به بعد">
                            <x-admin.form.input
                                name="later_points_percent"
                                type="number" min="0" max="100"
                                value="{{ old('later_points_percent', get_setting('gallery', 'later_points_percent', 25)) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="سقف رسانه‌های امتیازدار در ماه" hint="بیش از این تعداد در یک ماه شمسی تأیید می‌شود ولی امتیاز نمی‌گیرد. صفر = بدون سقف.">
                            <x-admin.form.input
                                name="monthly_rewarded_limit"
                                type="number" min="0"
                                value="{{ old('monthly_rewarded_limit', get_setting('gallery', 'monthly_rewarded_limit', 3)) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="سقف ارسال هر کاربر در ماه" hint="بعد از این تعداد، فرم ارسال تا ماه بعد بسته می‌شود. صفر = بدون سقف.">
                            <x-admin.form.input
                                name="monthly_submission_limit"
                                type="number" min="0"
                                value="{{ old('monthly_submission_limit', get_setting('gallery', 'monthly_submission_limit', 10)) }}"
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
                                        @php($mediaUrl = $submission->mediaUrl())
                                        @if($submission->type->value === 'image' && $mediaUrl)
                                            <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer" title="نمایش تصویر در اندازه اصلی">
                                                <img src="{{ $mediaUrl }}" alt="{{ $submission->title }}" loading="lazy"
                                                     class="h-14 w-20 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                                            </a>
                                        @elseif($submission->type->value === 'video' && $submission->videoThumbnailUrl())
                                            <img src="{{ $submission->videoThumbnailUrl() }}" alt="{{ $submission->title }}" loading="lazy"
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
                                        <div class="mt-1 flex flex-wrap gap-1.5 text-[10px] font-bold">
                                            @if($submission->order_number)
                                                <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-slate-600 dark:bg-slate-800 dark:text-slate-300" dir="ltr">سفارش: {{ $submission->order_number }}</span>
                                            @endif
                                            @if($submission->purchaser_mobile)
                                                <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-slate-600 dark:bg-slate-800 dark:text-slate-300" dir="ltr">{{ $submission->purchaser_mobile }}</span>
                                            @endif
                                        </div>
                                        @if($mediaUrl)
                                            <div class="mt-2 flex max-w-[320px] items-center gap-1.5" x-data="{ copied: false }">
                                                <input type="text" readonly value="{{ $mediaUrl }}" dir="ltr" onclick="this.select()"
                                                       class="h-8 min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-2 text-[11px] text-slate-600 outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer" download
                                                   class="inline-flex h-8 shrink-0 items-center rounded-lg bg-brand-500 px-2.5 text-[11px] font-extrabold text-white transition hover:bg-brand-600">
                                                    {{ $submission->type->value === 'video' ? 'باز کردن ویدیو' : 'دانلود' }}
                                                </a>
                                                <button type="button"
                                                        x-on:click="navigator.clipboard.writeText(@js($mediaUrl)); copied = true; setTimeout(() => copied = false, 1500)"
                                                        class="inline-flex h-8 shrink-0 cursor-pointer items-center rounded-lg border border-slate-200 px-2 text-[11px] font-extrabold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                                        x-text="copied ? 'کپی شد' : 'کپی'">کپی</button>
                                            </div>
                                        @else
                                            <div class="mt-1 text-[11px] font-bold text-red-500">لینک رسانه معتبر نیست (فقط http/https).</div>
                                        @endif
                                        @if($submission->type->value === 'video')
                                            <details class="mt-3 min-w-64 text-xs">
                                                <summary class="cursor-pointer font-bold text-brand-600 dark:text-brand-300">ویرایش ویدیو و تصویر شاخص</summary>
                                                <form method="POST" action="{{ route('admin.gallery.update', $submission) }}" class="mt-3 space-y-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <x-admin.field label="عنوان">
                                                        <x-admin.form.input name="title" :value="$submission->title" maxlength="120" required />
                                                    </x-admin.field>
                                                    <x-admin.field label="لینک ویدیو">
                                                        <x-admin.form.input name="video_url" type="url" dir="ltr" :value="$submission->video_url" maxlength="600" required />
                                                    </x-admin.field>
                                                    <x-admin.field label="لینک تصویر شاخص" hint="برای فایل ویدیویی مستقیم، تصویر پیش از پخش نمایش داده می‌شود. خالی = حذف تصویر شاخص.">
                                                        <x-admin.form.input name="video_thumbnail_url" type="url" dir="ltr" :value="$submission->video_thumbnail_url" maxlength="1000" />
                                                    </x-admin.field>
                                                    <button type="submit" class="rounded-xl bg-brand-500 px-3 py-2 font-bold text-white">ذخیره اطلاعات ویدیو</button>
                                                </form>
                                            </details>
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
                                                <form action="{{ route('admin.gallery.status', $submission) }}" method="post" class="flex items-center gap-1.5">
                                                    @csrf
                                                    @method('patch')
                                                    <input type="hidden" name="status" value="approved">
                                                    <label>
                                                        <input type="number" name="points" min="0" max="1000000" dir="ltr" placeholder="امتیاز"
                                                               title="خالی = امتیاز پیش‌فرض کاهشی (اولی تصویر {{ get_setting('gallery', 'image_points', 80) }} / ویدیو {{ get_setting('gallery', 'video_points', 200) }}، بعدی‌ها کمتر)"
                                                               class="h-9 w-24 rounded-xl border border-slate-200 bg-slate-50 px-2 text-xs font-bold text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:ring-emerald-500/10">
                                                    </label>
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
