@props([
    'mode' => 'variable',
    'manifest' => [],
])

@php
    $isSimple = $mode === 'simple';

    $defaults = [
        'download_type' => '',
        'archive_format' => null,
        'assembled_file_name' => null,

        'install_directory' => null,
        'requires_administrator' => false,

        'main_executable_path' => null,
        'working_directory' => '.',
        'launch_arguments' => null,

        'requires_sider' => false,
        'sider_executable_path' => null,
        'sider_working_directory' => null,
        'sider_arguments' => null,
        'run_sider_as_administrator' => false,

        'capabilities' => [
            'supports_install' => true,
            'supports_launch' => true,
            'supports_settings' => false,
            'supports_profiles' => false,
        ],

        'install_options' => [
            'extract_enabled' => true,
            'delete_archives_after_install' => true,
            'ask_before_delete_archives' => true,
            'clean_temp_after_install' => true,
            'create_desktop_shortcut' => false,
        ],
    ];

    $submittedManifest = is_array($manifest)
        ? $manifest
        : [];

    $manifestState = array_replace_recursive(
        $defaults,
        $submittedManifest
    );

    $state = $isSimple
        ? 'manifest'
        : 'item.manifest';

    $staticName = static function (string $path): string {
        return 'sale_items[0][manifest]['
            . implode('][', explode('.', $path))
            . ']';
    };

    $dynamicName = static function (string $path): string {
        return '`sale_items[${i}][manifest]['
            . implode('][', explode('.', $path))
            . ']`';
    };
@endphp

<div
    class="border-t border-slate-100 px-4 py-4"

    @if($isSimple)
        x-data="{
            manifest: {{ \Illuminate\Support\Js::from($manifestState) }}
        }"
    @endif
>
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h4 class="text-sm font-bold text-slate-900">
                تنظیمات لانچر
            </h4>

            <p class="mt-1 text-xs leading-6 text-slate-500">
                تنظیمات دانلود، نصب و اجرای این آیتم در نرم‌افزار ویندوز را مشخص کنید.
            </p>
        </div>

        <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700">
            Manifest
        </span>
    </div>

    {{-- Download --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">
                ساختار دانلود
            </div>

            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                مشخص کنید فایل‌ها مستقل هستند یا بخش‌های مختلف یک آرشیو چندقسمتی.
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-6 xl:col-span-4">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('download_type') }}"
                        x-bind:value="{{ $state }}.download_type"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('download_type') }}"
                        x-bind:value="{{ $state }}.download_type"
                    >
                @endif

                <x-admin.field
                    label="نوع دانلود"
                    hint="در حالت خودکار نیز Backend می‌تواند نوع دانلود را از فایل‌ها تشخیص دهد."
                >
                    <select
                        x-model="{{ $state }}.download_type"
                        class="h-10 w-full cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-orange-300 focus:ring-2 focus:ring-orange-200"
                    >
                        <option value="">
                            تشخیص خودکار
                        </option>

                        <option value="single_file">
                            تک فایل
                        </option>

                        <option value="multiple_files">
                            چند فایل مستقل
                        </option>

                        <option value="split_archive">
                            آرشیو چندقسمتی
                        </option>
                    </select>
                </x-admin.field>
            </div>

            <div
                class="col-span-12 md:col-span-6 xl:col-span-4"
                x-show="{{ $state }}.download_type === 'split_archive'"
                x-cloak
            >
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('archive_format') }}"
                        x-bind:value="{{ $state }}.archive_format"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('archive_format') }}"
                        x-bind:value="{{ $state }}.archive_format"
                    >
                @endif

                <x-admin.field
                    label="فرمت آرشیو"
                    hint="اگر خالی باشد، از نام فایل تشخیص داده می‌شود."
                >
                    <select
                        x-model="{{ $state }}.archive_format"
                        class="h-10 w-full cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-orange-300 focus:ring-2 focus:ring-orange-200"
                    >
                        <option value="">تشخیص خودکار</option>
                        <option value="7z">7z</option>
                        <option value="rar">RAR</option>
                        <option value="zip">ZIP</option>
                    </select>
                </x-admin.field>
            </div>

            <div
                class="col-span-12 md:col-span-6 xl:col-span-4"
                x-show="{{ $state }}.download_type === 'split_archive'"
                x-cloak
            >
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('assembled_file_name') }}"
                        x-bind:value="{{ $state }}.assembled_file_name"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('assembled_file_name') }}"
                        x-bind:value="{{ $state }}.assembled_file_name"
                    >
                @endif

                <x-admin.field
                    label="نام آرشیو نهایی"
                    hint="مثال: PGL_PATCH_V6.7z"
                >
                    <x-admin.form.input
                        x-model="{{ $state }}.assembled_file_name"
                        class="direction-ltr"
                        placeholder="PGL_PATCH_V6.7z"
                    />
                </x-admin.field>
            </div>
        </div>
    </div>

    {{-- Installation --}}
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">
                تنظیمات نصب
            </div>

            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                مسیر نصب و نحوه پردازش فایل‌های دانلودشده را مشخص کنید.
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-8">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('install_directory') }}"
                        x-bind:value="{{ $state }}.install_directory"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('install_directory') }}"
                        x-bind:value="{{ $state }}.install_directory"
                    >
                @endif

                <x-admin.field
                    label="مسیر نصب"
                    hint="مسیر باید نسبت به پوشه اصلی محصولات لانچر باشد."
                >
                    <x-admin.form.input
                        x-model="{{ $state }}.install_directory"
                        class="direction-ltr"
                        placeholder="InstalledProducts/PGL6"
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12 md:col-span-4">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('requires_administrator') }}"
                        x-bind:value="{{ $state }}.requires_administrator ? 1 : 0"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('requires_administrator') }}"
                        x-bind:value="{{ $state }}.requires_administrator ? 1 : 0"
                    >
                @endif

                <label
                    class="flex h-full min-h-20 cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200">
                    <input
                        type="checkbox"
                        x-model="{{ $state }}.requires_administrator"
                        class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    >

                    <span>
                        <span class="block text-xs font-bold text-slate-800">
                            دسترسی Administrator
                        </span>

                        <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                            نصب با سطح دسترسی مدیر اجرا شود.
                        </span>
                    </span>
                </label>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-12 gap-3">
            @php
                $installOptionCheckboxes = [
                    'extract_enabled' => [
                        'title' => 'استخراج فایل‌ها',
                        'description' => 'فایل‌های آرشیو پس از دانلود استخراج شوند.',
                    ],
                    'delete_archives_after_install' => [
                        'title' => 'حذف آرشیو بعد از نصب',
                        'description' => 'پس از نصب موفق، فایل‌های آرشیو حذف شوند.',
                    ],
                    'ask_before_delete_archives' => [
                        'title' => 'تایید قبل از حذف',
                        'description' => 'قبل از حذف فایل دانلودی از کاربر سوال شود.',
                    ],
                    'clean_temp_after_install' => [
                        'title' => 'پاکسازی فایل‌های موقت',
                        'description' => 'پوشه و فایل‌های موقت بعد از نصب حذف شوند.',
                    ],
                    'create_desktop_shortcut' => [
                        'title' => 'ساخت میانبر دسکتاپ',
                        'description' => 'پس از نصب، میانبر اجرا روی دسکتاپ ساخته شود.',
                    ],
                ];
            @endphp

            @foreach($installOptionCheckboxes as $key => $option)
                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    @if($isSimple)
                        <input
                            type="hidden"
                            name="{{ $staticName("install_options.{$key}") }}"
                            x-bind:value="{{ $state }}.install_options.{{ $key }} ? 1 : 0"
                        >
                    @else
                        <input
                            type="hidden"
                            x-bind:name="{{ $dynamicName("install_options.{$key}") }}"
                            x-bind:value="{{ $state }}.install_options.{{ $key }} ? 1 : 0"
                        >
                    @endif

                    <label
                        class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200">
                        <input
                            type="checkbox"
                            x-model="{{ $state }}.install_options.{{ $key }}"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                        >

                        <span>
                            <span class="block text-xs font-bold text-slate-800">
                                {{ $option['title'] }}
                            </span>

                            <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                                {{ $option['description'] }}
                            </span>
                        </span>
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Launch --}}
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">
                تنظیمات اجرا
            </div>

            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                فایل اجرایی و آرگومان‌های موردنیاز برای اجرای محصول را مشخص کنید.
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-6">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('main_executable_path') }}"
                        x-bind:value="{{ $state }}.main_executable_path"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('main_executable_path') }}"
                        x-bind:value="{{ $state }}.main_executable_path"
                    >
                @endif

                <x-admin.field
                    label="مسیر فایل اجرایی"
                    hint="مثال: PES2021.exe یا Game/PES2021.exe"
                >
                    <x-admin.form.input
                        x-model="{{ $state }}.main_executable_path"
                        class="direction-ltr"
                        placeholder="PES2021.exe"
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12 md:col-span-6">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('working_directory') }}"
                        x-bind:value="{{ $state }}.working_directory"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('working_directory') }}"
                        x-bind:value="{{ $state }}.working_directory"
                    >
                @endif

                <x-admin.field
                    label="پوشه اجرای برنامه"
                    hint="معمولا نقطه به معنی همان مسیر نصب است."
                >
                    <x-admin.form.input
                        x-model="{{ $state }}.working_directory"
                        class="direction-ltr"
                        placeholder="."
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('launch_arguments') }}"
                        x-bind:value="{{ $state }}.launch_arguments"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('launch_arguments') }}"
                        x-bind:value="{{ $state }}.launch_arguments"
                    >
                @endif

                <x-admin.field label="آرگومان‌های اجرا">
                    <x-admin.form.input
                        x-model="{{ $state }}.launch_arguments"
                        class="direction-ltr"
                        placeholder="-windowed -language=en"
                    />
                </x-admin.field>
            </div>
        </div>
    </div>

    {{-- Sider --}}
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        @if($isSimple)
            <input
                type="hidden"
                name="{{ $staticName('requires_sider') }}"
                x-bind:value="{{ $state }}.requires_sider ? 1 : 0"
            >
        @else
            <input
                type="hidden"
                x-bind:name="{{ $dynamicName('requires_sider') }}"
                x-bind:value="{{ $state }}.requires_sider ? 1 : 0"
            >
        @endif

        <label
            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200">
            <input
                type="checkbox"
                x-model="{{ $state }}.requires_sider"
                class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
            >

            <span>
                <span class="block text-xs font-bold text-slate-800">
                    اجرای محصول نیازمند Sider است
                </span>

                <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                    در صورت فعال بودن، لانچر ابتدا Sider و سپس فایل اصلی محصول را اجرا می‌کند.
                </span>
            </span>
        </label>

        <div
            class="mt-4 grid grid-cols-12 gap-4"
            x-show="{{ $state }}.requires_sider"
            x-cloak
        >
            <div class="col-span-12 md:col-span-6">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('sider_executable_path') }}"
                        x-bind:value="{{ $state }}.sider_executable_path"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('sider_executable_path') }}"
                        x-bind:value="{{ $state }}.sider_executable_path"
                    >
                @endif

                <x-admin.field label="مسیر فایل اجرایی Sider">
                    <x-admin.form.input
                        x-model="{{ $state }}.sider_executable_path"
                        class="direction-ltr"
                        placeholder="Sider/sider.exe"
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12 md:col-span-6">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('sider_working_directory') }}"
                        x-bind:value="{{ $state }}.sider_working_directory"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('sider_working_directory') }}"
                        x-bind:value="{{ $state }}.sider_working_directory"
                    >
                @endif

                <x-admin.field label="پوشه اجرای Sider">
                    <x-admin.form.input
                        x-model="{{ $state }}.sider_working_directory"
                        class="direction-ltr"
                        placeholder="Sider"
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('sider_arguments') }}"
                        x-bind:value="{{ $state }}.sider_arguments"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('sider_arguments') }}"
                        x-bind:value="{{ $state }}.sider_arguments"
                    >
                @endif

                <x-admin.field label="آرگومان‌های Sider">
                    <x-admin.form.input
                        x-model="{{ $state }}.sider_arguments"
                        class="direction-ltr"
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('run_sider_as_administrator') }}"
                        x-bind:value="{{ $state }}.run_sider_as_administrator ? 1 : 0"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('run_sider_as_administrator') }}"
                        x-bind:value="{{ $state }}.run_sider_as_administrator ? 1 : 0"
                    >
                @endif

                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3">
                    <input
                        type="checkbox"
                        x-model="{{ $state }}.run_sider_as_administrator"
                        class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    >

                    <span class="text-xs font-bold text-slate-800">
                        اجرای Sider با دسترسی Administrator
                    </span>
                </label>
            </div>
        </div>
    </div>

    {{-- Capabilities --}}
    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">
                قابلیت‌های لانچر
            </div>

            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                مشخص کنید چه امکاناتی برای این آیتم در نرم‌افزار نمایش داده شود.
            </div>
        </div>

        <div class="grid grid-cols-12 gap-3">
            @php
                $capabilityCheckboxes = [
                    'supports_install' => [
                        'title' => 'نصب محصول',
                        'description' => 'دکمه دانلود و نصب محصول نمایش داده شود.',
                    ],
                    'supports_launch' => [
                        'title' => 'اجرای محصول',
                        'description' => 'دکمه اجرای محصول بعد از نصب نمایش داده شود.',
                    ],
                    'supports_settings' => [
                        'title' => 'تنظیمات',
                        'description' => 'بخش تنظیمات محصول در لانچر فعال باشد.',
                    ],
                    'supports_profiles' => [
                        'title' => 'پروفایل‌ها',
                        'description' => 'امکان مدیریت پروفایل برای محصول فعال باشد.',
                    ],
                ];
            @endphp

            @foreach($capabilityCheckboxes as $key => $capability)
                <div class="col-span-12 md:col-span-6">
                    @if($isSimple)
                        <input
                            type="hidden"
                            name="{{ $staticName("capabilities.{$key}") }}"
                            x-bind:value="{{ $state }}.capabilities.{{ $key }} ? 1 : 0"
                        >
                    @else
                        <input
                            type="hidden"
                            x-bind:name="{{ $dynamicName("capabilities.{$key}") }}"
                            x-bind:value="{{ $state }}.capabilities.{{ $key }} ? 1 : 0"
                        >
                    @endif

                    <label
                        class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200">
                        <input
                            type="checkbox"
                            x-model="{{ $state }}.capabilities.{{ $key }}"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                        >

                        <span>
                            <span class="block text-xs font-bold text-slate-800">
                                {{ $capability['title'] }}
                            </span>

                            <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                                {{ $capability['description'] }}
                            </span>
                        </span>
                    </label>
                </div>
            @endforeach
        </div>

        <div class="mt-3 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-[11px] leading-6 text-blue-700">
            قابلیت‌های بررسی سلامت فایل، تعمیر و بروزرسانی در این نسخه غیرفعال هستند و پس از توسعه زیرساخت مربوطه اضافه
            می‌شوند.
        </div>
    </div>
</div>
