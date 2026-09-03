@props([
    'mode' => 'variable',
    'manifest' => [],
    // Only meaningful/passed for mode="simple" -- the "variable" mode reads the sale
    // item's id reactively from Alpine's own loop scope (item.id) instead, since many
    // rows share this one component instance as an x-for template.
    'saleItemId' => null,
])

@php
    $isSimple = $mode === 'simple';

    $defaults = [
        'download_type' => '',
        'archive_format' => null,
        'archive_password' => null,
        'assembled_file_name' => null,

        'install_directory' => null,
        'install_folder_name' => null,
        'requires_administrator' => false,
        'has_option_file' => false,

        'main_executable_path' => null,
        'working_directory' => '.',
        'launch_arguments' => null,

        'requires_sider' => false,
        'sider_executable_path' => null,
        'sider_working_directory' => '.',
        'sider_arguments' => null,
        'run_sider_as_administrator' => false,

        'content_key_id' => null,
        'content_key_value' => null,
        'content_key_configured' => false,

        'protected_folder_markers' => '',
        'protected_exact_paths' => '',
        'protected_excluded_paths' => '',
        'protected_modules' => [],
        'protected_strict_modules' => true,

        'cpu_compatibility' => [
            'enabled' => false,
            'default_mode' => 'off',
            'apply_to_game' => false,
            'apply_to_sider' => false,
            'allow_user_override' => false,
        ],

        'capabilities' => [
            'supports_install' => true,
            'supports_launch' => true,
            'supports_sider' => false,
            'supports_settings' => false,
            'supports_profiles' => false,
            'supports_update' => false,
            'supports_mod_manager' => false,
        ],

        'install_options' => [
            'extract_enabled' => true,
            'delete_archives_after_install' => true,
            'ask_before_delete_archives' => true,
            'clean_temp_after_install' => true,
            'create_desktop_shortcut' => false,
            'required_install_space_bytes' => null,
            'estimated_installed_size_bytes' => null,
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

    // A brand-new, not-yet-saved sale item has no manifest row to protect yet, so its
    // content-key fields start freely editable -- the vault only gates EXISTING
    // secrets. "variable" mode reads the id reactively (item.id) since one component
    // instance is reused as an x-for template across every row.
    $saleItemIdJs = $isSimple
        ? \Illuminate\Support\Js::from($saleItemId)
        : 'item.id ?? null';

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
    class="flex flex-col gap-4"
    @if($isSimple)
        x-data="{
            manifest: {{ \Illuminate\Support\Js::from($manifestState) }}
        }"
    @endif
>
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

                <x-admin.field label="نوع دانلود">
                    <x-admin.form.select x-model="{{ $state }}.download_type">
                        <option value="">تشخیص خودکار</option>
                        <option value="single_file">تک فایل</option>
                        <option value="multiple_files">چند فایل مستقل</option>
                        <option value="split_archive">آرشیو چندقسمتی</option>
                    </x-admin.form.select>
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

                <x-admin.field label="فرمت آرشیو">
                    <x-admin.form.select x-model="{{ $state }}.archive_format">
                        <option value="">تشخیص خودکار</option>
                        <option value="7z">7z</option>
                    </x-admin.form.select>
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

                <x-admin.field label="نام آرشیو نهایی">
                    <x-admin.form.input
                        x-model="{{ $state }}.assembled_file_name"
                        class="direction-ltr"
                    />
                </x-admin.field>
            </div>
            <div class="col-span-12 md:col-span-6 xl:col-span-4">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('archive_password') }}"
                        x-bind:value="{{ $state }}.archive_password"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('archive_password') }}"
                        x-bind:value="{{ $state }}.archive_password"
                    >
                @endif

                <x-admin.field
                    label="رمز دستی بسته پایه"
                    hint="همان رمز AES-256 فایل 7z است. در بسته چندپارچه برای تمام پارت‌ها یک رمز وارد کنید."
                >
                    <x-admin.form.input
                        type="password"
                        autocomplete="new-password"
                        x-model="{{ $state }}.archive_password"
                        class="direction-ltr"
                        placeholder="بدون رمز"
                    />
                </x-admin.field>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-6">
                @if($isSimple)
                    <input type="hidden" name="{{ $staticName('install_options.required_install_space_bytes') }}" x-bind:value="{{ $state }}.install_options.required_install_space_bytes">
                @else
                    <input type="hidden" x-bind:name="{{ $dynamicName('install_options.required_install_space_bytes') }}" x-bind:value="{{ $state }}.install_options.required_install_space_bytes">
                @endif
                <x-admin.field label="فضای موردنیاز نصب (Byte)" hint="حداقل فضای آزاد لازم؛ خالی یعنی تشخیص خودکار.">
                    <x-admin.form.input type="number" min="0" x-model="{{ $state }}.install_options.required_install_space_bytes" class="direction-ltr" placeholder="مثلاً 150000000000" />
                </x-admin.field>
            </div>
            <div class="col-span-12 md:col-span-6">
                @if($isSimple)
                    <input type="hidden" name="{{ $staticName('install_options.estimated_installed_size_bytes') }}" x-bind:value="{{ $state }}.install_options.estimated_installed_size_bytes">
                @else
                    <input type="hidden" x-bind:name="{{ $dynamicName('install_options.estimated_installed_size_bytes') }}" x-bind:value="{{ $state }}.install_options.estimated_installed_size_bytes">
                @endif
                <x-admin.field label="حجم تقریبی نصب‌شده (Byte)" hint="برای نمایش به کاربر؛ خالی یعنی اندازه واقعی پس از نصب محاسبه شود.">
                    <x-admin.form.input type="number" min="0" x-model="{{ $state }}.install_options.estimated_installed_size_bytes" class="direction-ltr" placeholder="مثلاً 120000000000" />
                </x-admin.field>
            </div>
        </div>
    </div>

    {{-- Installation --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">
                تنظیمات نصب
            </div>

            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                مسیر نصب و نحوه پردازش فایل‌های دانلودشده را مشخص کنید.
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12">
                @if($isSimple)
                    <input
                        type="hidden"
                        name="{{ $staticName('install_folder_name') }}"
                        x-bind:value="{{ $state }}.install_folder_name"
                    >
                @else
                    <input
                        type="hidden"
                        x-bind:name="{{ $dynamicName('install_folder_name') }}"
                        x-bind:value="{{ $state }}.install_folder_name"
                    >
                @endif

                <x-admin.field label="نام پوشه محصول در MW Launcher">
                    <x-admin.form.input
                        x-model="{{ $state }}.install_folder_name"
                        class="direction-ltr"
                        placeholder="PGLV6 S2026-2027"
                    />
                </x-admin.field>
                <p class="mt-1 text-[11px] text-slate-500">
                    لانچر این پوشه را داخل مسیر اصلی انتخاب‌شده توسط کاربر می‌سازد.
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-12 gap-3">
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
                        class="h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500"
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

            <div class="col-span-12 md:col-span-4">
                @if($isSimple)
                    <input type="hidden"
                           name="{{ $staticName('has_option_file') }}"
                           x-bind:value="{{ $state }}.has_option_file ? 1 : 0">
                @else
                    <input type="hidden"
                           x-bind:name="{{ $dynamicName('has_option_file') }}"
                           x-bind:value="{{ $state }}.has_option_file ? 1 : 0">
                @endif

                <label class="flex h-full min-h-20 cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200">
                    <input type="checkbox"
                           x-model="{{ $state }}.has_option_file"
                           class="h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                    <span>
                        <span class="block text-xs font-bold text-slate-800">دارای OPTION FILE</span>
                        <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                            پوشه <span dir="ltr" class="font-semibold">OPTION FILE</span> باید بعد از استخراج در ریشه بسته وجود داشته باشد و در Windows Documents ادغام می‌شود.
                        </span>
                    </span>
                </label>
            </div>

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
                            class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500"
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
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
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

                <x-admin.field label="مسیر فایل اجرایی">
                    <x-admin.form.input
                        x-model="{{ $state }}.main_executable_path"
                        class="direction-ltr"
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

                <x-admin.field label="پوشه اجرای برنامه">
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
                    />
                </x-admin.field>
            </div>
        </div>
    </div>

    {{-- Sider --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">
                تنظیمات Sider
            </div>

            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                پشتیبانی از Sider و اجباری بودن اجرای آن برای هر نسخه فروش مستقل است.
            </div>
        </div>

        @if($isSimple)
            <input
                type="hidden"
                name="{{ $staticName('capabilities.supports_sider') }}"
                x-bind:value="{{ $state }}.capabilities.supports_sider ? 1 : 0"
            >
            <input
                type="hidden"
                name="{{ $staticName('requires_sider') }}"
                x-bind:value="{{ $state }}.requires_sider ? 1 : 0"
            >
        @else
            <input
                type="hidden"
                x-bind:name="{{ $dynamicName('capabilities.supports_sider') }}"
                x-bind:value="{{ $state }}.capabilities.supports_sider ? 1 : 0"
            >
            <input
                type="hidden"
                x-bind:name="{{ $dynamicName('requires_sider') }}"
                x-bind:value="{{ $state }}.requires_sider ? 1 : 0"
            >
        @endif

        <div class="grid grid-cols-12 gap-3">
            <div class="col-span-12 md:col-span-6">
                <label
                    class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200">
                    <input
                        type="checkbox"
                        x-model="{{ $state }}.capabilities.supports_sider"
                        x-on:change="
                            if (!{{ $state }}.capabilities.supports_sider) {
                                {{ $state }}.requires_sider = false;
                                {{ $state }}.cpu_compatibility.apply_to_sider = false;
                            }
                        "
                        class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    >

                    <span>
                        <span class="block text-xs font-bold text-slate-800">
                            پشتیبانی از Sider
                        </span>

                        <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                            لانچر می‌تواند Sider این نسخه را اجرا کند. این گزینه به معنی اجباری بودن Sider نیست.
                        </span>
                    </span>
                </label>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label
                    class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200"
                    x-bind:class="!{{ $state }}.capabilities.supports_sider ? 'opacity-60' : ''"
                >
                    <input
                        type="checkbox"
                        x-model="{{ $state }}.requires_sider"
                        x-bind:disabled="!{{ $state }}.capabilities.supports_sider"
                        class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500 disabled:cursor-not-allowed"
                    >

                    <span>
                        <span class="block text-xs font-bold text-slate-800">
                            اجرای Sider اجباری است
                        </span>

                        <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                            فقط وقتی فعال کنید که محصول بدون اجرای Sider نباید توسط لانچر اجرا شود.
                        </span>
                    </span>
                </label>
            </div>
        </div>

        <div
            class="mt-4 grid grid-cols-12 gap-4"
            x-show="{{ $state }}.capabilities.supports_sider || {{ $state }}.requires_sider"
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
                        placeholder="sider.exe"
                    />
                </x-admin.field>
                <p class="mt-1 text-[11px] text-slate-500">
                    وقتی Sider برای این نسخه فعال است، این مسیر الزامی است.
                </p>
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
                        placeholder="."
                    />
                </x-admin.field>
                <p class="mt-1 text-[11px] text-slate-500">
                    این مقدار نیز برای Sider فعال الزامی است؛ معمولا مقدار آن <span class="direction-ltr">.</span> است.
                </p>
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
                        class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    >

                    <span>
                        <span class="block text-xs font-bold text-slate-800">
                            اجرای Sider با دسترسی Administrator
                        </span>
                        <span class="mt-1 block text-[11px] leading-5 text-amber-700">
                            ترجیحا غیرفعال بماند تا لانچر بتواند CPU Affinity را روی پردازش Sider اعمال کند.
                        </span>
                    </span>
                </label>
            </div>
        </div>
    </div>

    {{-- Content Key (encrypted database protection) --}}
    <div
        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
        x-data="{
            saleItemId: {{ $saleItemIdJs }},
            vaultUnlocked: false,
            vaultPassword: '',
            vaultError: '',
            vaultBusy: false,
            get vaultRequired() {
                return this.saleItemId !== null && this.saleItemId !== undefined && !this.vaultUnlocked;
            },
            async unlockVault() {
                if (!this.vaultPassword) return;
                this.vaultBusy = true;
                this.vaultError = '';
                try {
                    const response = await fetch('{{ route('admin.products.content-key.reveal') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.content ?? '',
                        },
                        body: JSON.stringify({
                            sale_item_id: this.saleItemId,
                            vault_password: this.vaultPassword,
                        }),
                    });
                    const data = await response.json();
                    if (!response.ok || data.ok !== true) {
                        this.vaultError = data.message || 'باز کردن صندوق ناموفق بود.';
                        return;
                    }
                    {{ $state }}.content_key_id = data.content_key_id || {{ $state }}.content_key_id;
                    {{ $state }}.content_key_value = data.content_key_value || '';
                    this.vaultUnlocked = true;
                    this.vaultPassword = '';
                } catch (e) {
                    this.vaultError = 'ارتباط با سرور برقرار نشد.';
                } finally {
                    this.vaultBusy = false;
                }
            },
            generateKey() {
                const bytes = new Uint8Array(32);
                window.crypto.getRandomValues(bytes);
                let binary = '';
                bytes.forEach(b => binary += String.fromCharCode(b));
                {{ $state }}.content_key_value = window.btoa(binary);
            }
        }"
    >
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">
                محافظت محتوا (کلید رمزگشایی دیتابیس)
            </div>

            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                فقط برای محصولاتی که دیتابیس بازی از قبل رمزنگاری شده پر کنید (مثلاً PGL6).
                برای بقیه‌ی محصولات هر دو فیلد را خالی بگذارید. جزئیات کامل در
                <span dir="ltr" class="font-mono">docs/CONTENT-KEY-PIPE-FA.md</span>
                سمت لانچر.
            </div>
        </div>

        {{-- Hidden inputs submit whatever is currently in Alpine state regardless of
             lock status -- an existing key that was never unlocked simply stays as the
             empty string the server already redacted, so the update request never
             overwrites it (see UpdateProductRequest's untouched-when-locked check). --}}
        @if($isSimple)
            <input type="hidden" name="{{ $staticName('content_key_id') }}" x-bind:value="{{ $state }}.content_key_id">
            <input type="hidden" name="{{ $staticName('content_key_value') }}" x-bind:value="{{ $state }}.content_key_value">
        @else
            <input type="hidden" x-bind:name="{{ $dynamicName('content_key_id') }}" x-bind:value="{{ $state }}.content_key_id">
            <input type="hidden" x-bind:name="{{ $dynamicName('content_key_value') }}" x-bind:value="{{ $state }}.content_key_value">
        @endif

        <template x-if="vaultRequired">
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <div class="mb-2 flex items-center gap-2 text-xs font-bold text-amber-800">
                    <span>🔒</span>
                    <span>
                        این نسخه فروش
                        <template x-if="{{ $state }}.content_key_configured">
                            <span>یک کلید محتوای ثبت‌شده دارد</span>
                        </template>
                        <template x-if="!{{ $state }}.content_key_configured">
                            <span>هنوز کلید محتوایی ندارد</span>
                        </template>
                        — برای مشاهده یا تغییر آن، رمز صندوق کلید محتوا را وارد کنید.
                    </span>
                </div>

                <div class="flex flex-col gap-2 md:flex-row">
                    <input
                        type="password"
                        x-model="vaultPassword"
                        x-on:keydown.enter.prevent="unlockVault()"
                        autocomplete="off"
                        placeholder="رمز صندوق کلید محتوا"
                        class="h-10 flex-1 rounded-xl border border-amber-300 bg-white px-3 text-sm direction-ltr outline-none focus:border-amber-500"
                    >
                    <button
                        type="button"
                        x-on:click="unlockVault()"
                        x-bind:disabled="vaultBusy || !vaultPassword"
                        class="h-10 shrink-0 rounded-xl bg-amber-600 px-4 text-xs font-bold text-white transition hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span x-show="!vaultBusy">باز کردن</span>
                        <span x-show="vaultBusy">در حال بررسی...</span>
                    </button>
                </div>

                <p class="mt-2 text-[11px] leading-5 text-rose-600" x-show="vaultError" x-text="vaultError"></p>
            </div>
        </template>

        <div class="grid grid-cols-12 gap-4" x-show="!vaultRequired" x-cloak>
            <div class="col-span-12 md:col-span-6">
                <x-admin.field
                    label="شناسه کلید محتوا"
                    hint="یک شناسه‌ی نسخه‌دار و یکتا، نه اسم محصول -- مثلاً pgl6-db-2026-01. با هر بار رمزنگاری مجدد دیتابیس، این شناسه را عوض کنید."
                >
                    <x-admin.form.input
                        x-model="{{ $state }}.content_key_id"
                        class="direction-ltr"
                        placeholder="pgl6-db-2026-01"
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12 md:col-span-6">
                <x-admin.field
                    label="مقدار کلید محتوا (Base64، دقیقاً ۳۲ بایت)"
                    hint="همان کلیدی که دیتابیس با آن رمزنگاری شده -- باید کاراکتر به کاراکتر با آنچه در اسکریپت رمزنگاری استفاده کردید یکی باشد."
                >
                    <div class="flex gap-2">
                        <x-admin.form.input
                            x-model="{{ $state }}.content_key_value"
                            class="direction-ltr flex-1"
                            placeholder="Base64..."
                            autocomplete="off"
                        />
                        <button
                            type="button"
                            x-on:click="generateKey()"
                            class="shrink-0 rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-bold text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                        >
                            تولید تصادفی
                        </button>
                    </div>
                    <p class="mt-1 text-[11px] leading-5 text-amber-700">
                        کلید تولیدشده را همین‌جا کپی کنید و دقیقاً همان مقدار را در اسکریپت
                        رمزنگاری دیتابیس هم استفاده کنید -- این دو باید کاراکتر به کاراکتر یکی باشند.
                    </p>
                </x-admin.field>
            </div>
        </div>
    </div>

    {{-- Content protection policy: what sider.dll protects and which Lua modules it
         may run. Unlike the key value above this is not secret (paths, plus digests
         of files the customer already has), so it needs no vault gate -- it only
         needs to be editable by admins and nobody else. It lives here instead of
         compiled into pgldrm.cpp so that protecting a new file or updating a module
         is an edit on this page rather than a sider.dll rebuild and a new launcher
         release. --}}
    <div
        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
        x-data="{
            modulesError: '',
            loadModulesFile(event) {
                this.modulesError = '';
                const file = event.target.files && event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = () => {
                    try {
                        const parsed = JSON.parse(reader.result);
                        // Accepts either the file hash_lua_modules.py writes
                        // ({modules: [...]}) or a bare array, so a hand-trimmed
                        // list still works.
                        const list = Array.isArray(parsed) ? parsed : parsed.modules;
                        if (!Array.isArray(list)) {
                            this.modulesError = 'ساختار فایل معتبر نیست: کلید modules پیدا نشد.';
                            return;
                        }

                        const cleaned = [];
                        for (const entry of list) {
                            const path = (entry && entry.path ? String(entry.path) : '').trim();
                            const tier = (entry && entry.tier ? String(entry.tier) : 'shipped').trim().toLowerCase();
                            const sha256 = (entry && entry.sha256 ? String(entry.sha256) : '').trim().toLowerCase();
                            // Rejected here as well as server-side: catching a bad
                            // file at upload time tells the admin which line is wrong,
                            // instead of silently dropping the module and leaving them
                            // to discover it when the game stops loading it.
                            if (!path || /[\r\n]/.test(path)) continue;
                            if (tier !== 'content' && tier !== 'shipped') continue;
                            if (!/^[a-f0-9]{64}$/.test(sha256)) continue;
                            cleaned.push({ path, tier, sha256 });
                        }

                        if (!cleaned.length) {
                            this.modulesError = 'هیچ ماژول معتبری در فایل پیدا نشد.';
                            return;
                        }
                        if (cleaned.length !== list.length) {
                            this.modulesError = (list.length - cleaned.length) + ' ردیف نامعتبر نادیده گرفته شد.';
                        }
                        {{ $state }}.protected_modules = cleaned;
                    } catch (error) {
                        this.modulesError = 'خواندن فایل JSON ناموفق بود.';
                    } finally {
                        event.target.value = '';
                    }
                };
                reader.readAsText(file);
            },
        }"
    >
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">سیاست محافظت محتوا</div>
            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                فقط برای محصولاتی که شناسه کلید محتوا دارند اعمال می‌شود. هر خط یک مسیر.
                این مقادیر محرمانه نیستند و رمزنگاری نمی‌شوند؛ اهمیتشان این است که فقط مدیر بتواند تغییرشان دهد.
            </div>
        </div>

        @php
            $protectionFields = [
                'protected_folder_markers' => [
                    'label' => 'پوشه‌های کاملاً محافظت‌شده',
                    'hint' => 'مثل pesdb — فقط وقتی مجاز است که همه فایل‌های داخل آن پوشه مال ما و رمزشده باشند. اگر فایل سالم بازی را در بر بگیرد، آن فایل خراب می‌شود.',
                    'placeholder' => "pesdb",
                ],
                'protected_exact_paths' => [
                    'label' => 'فایل‌های محافظت‌شده (مسیر کامل)',
                    'hint' => 'برای پوشه‌هایی که فایل‌های اصلی بازی هم داخلشان هست (مثل common\\menu\\licence که حدود ۱۰۰۰ فایل سالم کنار چند فایل ما دارد).',
                    'placeholder' => "common\\menu\\licence\\game2dpes_perest.bin",
                ],
                'protected_excluded_paths' => [
                    'label' => 'استثناها (همیشه بدون رمز)',
                    'hint' => 'قبل از دو لیست بالا بررسی می‌شود. فایل‌هایی که باید plain بمانند هرچند داخل پوشه‌ی محافظت‌شده‌اند.',
                    'placeholder' => "\\pesdb\\competition.bin",
                ],
            ];
        @endphp

        <div class="grid grid-cols-12 gap-4">
            @foreach($protectionFields as $field => $meta)
                <div class="col-span-12 md:col-span-4">
                    <x-admin.field :label="$meta['label']" :hint="$meta['hint']">
                        <textarea
                            x-model="{{ $state }}.{{ $field }}"
                            rows="5"
                            dir="ltr"
                            placeholder="{{ $meta['placeholder'] }}"
                            class="w-full rounded-xl border border-slate-200 bg-white p-3 font-mono text-[11px] leading-5 outline-none focus:border-orange-400"
                        ></textarea>
                    </x-admin.field>
                    @if($isSimple)
                        <input type="hidden" name="{{ $staticName($field) }}" x-bind:value="{{ $state }}.{{ $field }}">
                    @else
                        <input type="hidden" x-bind:name="{{ $dynamicName($field) }}" x-bind:value="{{ $state }}.{{ $field }}">
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-bold text-slate-800">ماژول‌های Lua مجاز</div>
                    <div class="mt-1 text-[11px] leading-5 text-slate-500">
                        خروجی <span dir="ltr" class="font-mono">hash_lua_modules.py</span> را اینجا آپلود کنید.
                        هر ماژول با مسیر و SHA-256 دقیقش ثبت می‌شود، پس فایلی با همان نام ولی محتوای متفاوت پذیرفته نمی‌شود.
                    </div>
                </div>
                <div class="text-[11px] font-bold text-slate-600">
                    <span x-text="({{ $state }}.protected_modules || []).length"></span>
                    ماژول ثبت‌شده
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <input
                    type="file"
                    accept=".json,application/json"
                    x-on:change="loadModulesFile($event)"
                    class="text-[11px] file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-[11px] file:font-bold file:text-slate-700"
                >
                <button
                    type="button"
                    x-on:click="{{ $state }}.protected_modules = []"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-[11px] font-bold text-slate-600 transition hover:border-rose-300 hover:text-rose-600"
                >
                    پاک‌کردن لیست
                </button>
                <p class="text-[11px] font-bold text-rose-600" x-show="modulesError" x-text="modulesError"></p>
            </div>

            {{-- Serialized as JSON in one hidden field rather than as an input per
                 module: a 50-module list would otherwise mean 150 hidden inputs, and
                 Alpine would have to keep them all in sync on every upload. --}}
            @if($isSimple)
                <input type="hidden" name="{{ $staticName('protected_modules_json') }}"
                       x-bind:value="JSON.stringify({{ $state }}.protected_modules || [])">
            @else
                <input type="hidden" x-bind:name="{{ $dynamicName('protected_modules_json') }}"
                       x-bind:value="JSON.stringify({{ $state }}.protected_modules || [])">
            @endif

            <div class="mt-3 max-h-56 overflow-y-auto rounded-lg border border-slate-100"
                 x-show="({{ $state }}.protected_modules || []).length">
                <template x-for="entry in ({{ $state }}.protected_modules || [])" :key="entry.path">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-50 px-3 py-2 last:border-b-0">
                        <span class="truncate font-mono text-[11px] text-slate-700" dir="ltr" x-text="entry.path"></span>
                        <span
                            class="shrink-0 rounded px-2 py-1 text-[10px] font-black"
                            x-bind:class="entry.tier === 'content' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'"
                            x-text="entry.tier === 'content' ? 'محتوای محافظت‌شده' : 'عادی'"
                        ></span>
                    </div>
                </template>
            </div>

            <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                <input type="checkbox" x-model="{{ $state }}.protected_strict_modules" class="mt-1 h-4 w-4">
                <span class="text-[11px] leading-5 text-slate-700">
                    <span class="font-bold">فقط ماژول‌های مجاز اجرا شوند</span>
                    — ماژول‌های خارج از این لیست اصلاً بارگذاری نمی‌شوند. اگر خاموش باشد،
                    آن ماژول‌ها اجرا می‌شوند ولی به کتابخانه‌ی <span dir="ltr" class="font-mono">memory</span>
                    و محتوای رمزگشایی‌شده دسترسی ندارند.
                </span>
            </label>
            @if($isSimple)
                <input type="hidden" name="{{ $staticName('protected_strict_modules') }}"
                       x-bind:value="{{ $state }}.protected_strict_modules ? 1 : 0">
            @else
                <input type="hidden" x-bind:name="{{ $dynamicName('protected_strict_modules') }}"
                       x-bind:value="{{ $state }}.protected_strict_modules ? 1 : 0">
            @endif
        </div>
    </div>

    {{-- CPU Compatibility --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <div class="text-xs font-bold text-slate-800">
                CPU Compatibility
            </div>

            <div class="mt-1 text-[11px] leading-5 text-slate-500">
                تنظیمات سازگاری پردازنده برای همین نسخه فروش. برای پچ‌های PES می‌توانید آن را فقط روی Sider اعمال کنید.
            </div>
        </div>

        @foreach([
            'enabled',
            'apply_to_game',
            'apply_to_sider',
            'allow_user_override',
        ] as $cpuBooleanField)
            @if($isSimple)
                <input
                    type="hidden"
                    name="{{ $staticName("cpu_compatibility.{$cpuBooleanField}") }}"
                    x-bind:value="{{ $state }}.cpu_compatibility.{{ $cpuBooleanField }} ? 1 : 0"
                >
            @else
                <input
                    type="hidden"
                    x-bind:name="{{ $dynamicName("cpu_compatibility.{$cpuBooleanField}") }}"
                    x-bind:value="{{ $state }}.cpu_compatibility.{{ $cpuBooleanField }} ? 1 : 0"
                >
            @endif
        @endforeach

        @if($isSimple)
            <input
                type="hidden"
                name="{{ $staticName('cpu_compatibility.default_mode') }}"
                x-bind:value="{{ $state }}.cpu_compatibility.default_mode"
            >
        @else
            <input
                type="hidden"
                x-bind:name="{{ $dynamicName('cpu_compatibility.default_mode') }}"
                x-bind:value="{{ $state }}.cpu_compatibility.default_mode"
            >
        @endif

        <label
            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200">
            <input
                type="checkbox"
                x-model="{{ $state }}.cpu_compatibility.enabled"
                class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500"
            >

            <span>
                <span class="block text-xs font-bold text-slate-800">
                    فعال‌سازی CPU Compatibility
                </span>
                <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                    در صورت فعال بودن، لانچر بر اساس تنظیمات زیر سازگاری CPU را اعمال می‌کند.
                </span>
            </span>
        </label>

        <div
            class="mt-4 grid grid-cols-12 gap-3"
            x-show="{{ $state }}.cpu_compatibility.enabled"
            x-cloak
        >
            <div class="col-span-12 md:col-span-6">
                <x-admin.field label="حالت پیش‌فرض">
                    <x-admin.form.select x-model="{{ $state }}.cpu_compatibility.default_mode">
                        <option value="off">خاموش</option>
                        <option value="auto">خودکار — فقط CPU هیبریدی</option>
                        <option value="manual">دستی — پیش‌فرض خاموش</option>
                    </x-admin.form.select>
                    <p class="mt-1 text-[11px] leading-5 text-slate-500">
                        در حالت خودکار، پردازنده‌های قدیمی و غیرهیبریدی بدون تغییر اجرا می‌شوند. حالت دستی نیازمند اجازه تغییر توسط کاربر است.
                    </p>
                </x-admin.field>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3">
                    <input
                        type="checkbox"
                        x-model="{{ $state }}.cpu_compatibility.allow_user_override"
                        class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    >
                    <span>
                        <span class="block text-xs font-bold text-slate-800">اجازه تغییر توسط کاربر</span>
                        <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                            کاربر بتواند حالت سازگاری را در لانچر تغییر دهد.
                        </span>
                    </span>
                </label>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3">
                    <input
                        type="checkbox"
                        x-model="{{ $state }}.cpu_compatibility.apply_to_game"
                        class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                    >
                    <span>
                        <span class="block text-xs font-bold text-slate-800">اعمال روی بازی</span>
                        <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                            برای پچ‌هایی که خود بازی مشکل CPU ندارد، این گزینه باید غیرفعال باشد.
                        </span>
                    </span>
                </label>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label
                    class="flex h-full cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3"
                    x-bind:class="!{{ $state }}.capabilities.supports_sider ? 'opacity-60' : ''"
                >
                    <input
                        type="checkbox"
                        x-model="{{ $state }}.cpu_compatibility.apply_to_sider"
                        x-bind:disabled="!{{ $state }}.capabilities.supports_sider"
                        class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500 disabled:cursor-not-allowed"
                    >
                    <span>
                        <span class="block text-xs font-bold text-slate-800">اعمال روی Sider</span>
                        <span class="mt-1 block text-[11px] leading-5 text-slate-500">
                            برای مشکل تعداد هسته هنگام اجرای Sider این گزینه را فعال کنید.
                        </span>
                    </span>
                </label>
            </div>
        </div>
    </div>

    {{-- Capabilities --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
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
                    'supports_update' => [
                        'title' => 'آپدیت محصول',
                        'description' => 'آپدیت‌های فعال همین نسخه در لانچر نمایش داده شوند.',
                    ],
                    'supports_mod_manager' => [
                        'title' => 'مادمنیجر',
                        'description' => 'مادهای همین نسخه فروش در لانچر مدیریت شوند.',
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
                            class="mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-500"
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
    </div>
</div>
