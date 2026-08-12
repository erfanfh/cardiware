@props(['mode' => 'variable'])

@php
    $item = $mode === 'simple' ? 'saleItems[0]' : 'item';
    $indexToken = $mode === 'simple' ? '${0}' : '${i}';
@endphp

<div class="space-y-4" x-show="{{ $item }}">
    {{-- Publish in launcher --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div
            class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/70 px-4 py-3">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h4 class="text-sm font-bold text-slate-900">انتشار در MW Launcher</h4>
                    <span class="rounded-full bg-orange-50 px-2.5 py-1 text-[11px] font-bold text-orange-700">
                        Sale Item مستقل
                    </span>
                </div>
                <p class="mt-1 text-xs leading-6 text-slate-500">
                    عنوان، Hero و محتوای لانچر فقط برای همین نسخه فروش ذخیره می‌شود.
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2">
                <div>
                    <div class="text-xs font-bold text-slate-800">نمایش در لانچر</div>
                    <div class="mt-0.5 text-[11px] text-slate-500">این نسخه در کاتالوگ MW Launcher منتشر شود.</div>
                </div>

                <x-admin.form.switch
                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_enabled]`"
                    x-model="{{ $item }}.launcher_enabled"
                />
            </div>
        </div>

        <div class="p-4">
            <div class="grid grid-cols-12 gap-4">
                <x-admin.field label="عنوان در لانچر" class="col-span-12 md:col-span-9">
                    <x-admin.form.input
                        x-bind:name="`sale_items[{{ $indexToken }}][launcher_title]`"
                        x-model="{{ $item }}.launcher_title"
                        placeholder="عنوانی که داخل MW Launcher نمایش داده می‌شود"
                    />
                </x-admin.field>

                <x-admin.field label="ترتیب نمایش" class="col-span-12 md:col-span-3">
                    <x-admin.form.input
                        type="number"
                        min="0"
                        x-bind:name="`sale_items[{{ $indexToken }}][launcher_sort_order]`"
                        x-model="{{ $item }}.launcher_sort_order"
                        placeholder="0"
                    />
                </x-admin.field>

                <x-admin.field label="توضیح کوتاه لانچر" class="col-span-12">
                    <x-admin.form.textarea
                        rows="2"
                        x-bind:name="`sale_items[{{ $indexToken }}][launcher_excerpt]`"
                        x-model="{{ $item }}.launcher_excerpt"
                        placeholder="توضیح کوتاهی که در کارت یا جزئیات این نسخه نمایش داده می‌شود"
                    />
                </x-admin.field>

                <div class="col-span-12 rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-slate-900">
                                فعال بودن پشتیبانی آنلاین
                            </div>

                            <div class="mt-1 text-xs leading-6 text-slate-500">
                                در صورت فعال بودن، لانچر لینک مستقیم صفحه پشتیبانی همین Sale Item را دریافت میکند.
                            </div>
                        </div>

                        <div class="shrink-0">
                            <x-admin.form.switch
                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_support_enabled]`"
                                x-model="{{ $item }}.launcher_support_enabled"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-4"
                        x-show="{{ $item }}.launcher_support_enabled"
                        x-cloak
                    >
                        <x-admin.field
                            label="آدرس کامل صفحه پشتیبانی"
                            hint="آدرس باید کامل و با https:// شروع شود."
                        >
                            <x-admin.form.input
                                type="url"
                                dir="ltr"
                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_support_url]`"
                                x-model="{{ $item }}.launcher_support_url"
                                x-bind:required="{{ $item }}.launcher_support_enabled"
                                placeholder="https://moddingway.ir/..."
                                class="text-left"
                            />
                        </x-admin.field>
                    </div>
                </div>

                <div
                    class="col-span-12"
                    x-on:mfip-selected.window="
                        if ($event.detail.itemKey !== ({{ $item }}.__key + '-launcher-hero')) return;
                        {{ $item }}.launcher_hero = {
                            id: $event.detail.mediaId ?? null,
                            path: $event.detail.mediaPath ?? null,
                        };
                    "
                    x-on:mfip-cleared.window="
                        if ($event.detail.itemKey !== ({{ $item }}.__key + '-launcher-hero')) return;
                        {{ $item }}.launcher_hero = { id: null, path: null };
                    "
                >
                    <x-admin.field
                        label="تصویر Hero لانچر"
                        hint="این تصویر فقط برای همین Sale Item استفاده می‌شود و در صورت انتخاب نشدن، لانچر مقدار null دریافت می‌کند."
                    >
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <button
                                type="button"
                                class="block w-full cursor-pointer p-3 text-right outline-none transition hover:bg-slate-50 focus:ring-2 focus:ring-inset focus:ring-orange-200"
                                @click="$dispatch('mfip-open-requested', {
                                    itemKey: ({{ $item }}.__key + '-launcher-hero'),
                                    selectedMediaFileId: {{ $item }}.launcher_hero?.id ?? null,
                                    selectedMediaPath: {{ $item }}.launcher_hero?.path ?? null
                                })"
                            >
                                <template x-if="{{ $item }}.launcher_hero?.path">
                                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                                        <div
                                            class="h-32 w-full shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 md:w-56">
                                            <img
                                                :src="{{ $item }}.launcher_hero.path"
                                                alt=""
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            >
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="text-sm font-bold text-slate-900">تصویر Hero انتخاب شده
                                                </div>
                                                <span
                                                    class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700">آماده</span>
                                            </div>

                                            <div
                                                class="direction-ltr mt-1 truncate text-left text-xs text-slate-500"
                                                x-text="{{ $item }}.launcher_hero.path"
                                            ></div>

                                            <div
                                                class="mt-3 inline-flex h-9 cursor-pointer items-center justify-center rounded-3xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                                تغییر تصویر
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!{{ $item }}.launcher_hero?.path">
                                    <div
                                        class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 px-4 py-7 text-center transition hover:border-orange-300 hover:bg-orange-50/30">
                                        <div
                                            class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-lg text-slate-400 shadow-sm">
                                            ✦
                                        </div>
                                        <div class="mt-3 text-sm font-bold text-slate-800">تصویر Hero انتخاب نشده است
                                        </div>
                                        <div class="mx-auto mt-1 max-w-xl text-xs leading-6 text-slate-500">
                                            برای انتخاب یا آپلود تصویر از کتابخانه رسانه کلیک کنید.
                                        </div>
                                        <div
                                            class="mt-3 inline-flex h-9 cursor-pointer items-center justify-center rounded-3xl bg-slate-900 px-4 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800">
                                            انتخاب تصویر
                                        </div>
                                    </div>
                                </template>
                            </button>

                            <div
                                class="flex justify-end border-t border-slate-100 px-3 py-3"
                                x-show="{{ $item }}.launcher_hero?.id"
                                x-cloak
                            >
                                <x-admin.form.button
                                    type="button"
                                    variant="danger"
                                    size="sm"
                                    class="cursor-pointer"
                                    @click.stop="{{ $item }}.launcher_hero = { id: null, path: null }"
                                >
                                    حذف تصویر
                                </x-admin.form.button>
                            </div>
                        </div>

                        <input
                            type="hidden"
                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_hero][id]`"
                            x-model="{{ $item }}.launcher_hero.id"
                        >
                        <input
                            type="hidden"
                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_hero][path]`"
                            x-model="{{ $item }}.launcher_hero.path"
                        >
                    </x-admin.field>
                </div>
            </div>
        </div>
    </section>

    {{-- Updates --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div
            class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-4 py-3">
            <div>
                <div class="text-sm font-bold text-slate-900">آپدیت‌های محصول</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">نسخه‌های قابل دریافت بعد از نصب Base را برای همین
                    Sale Item مدیریت کنید.
                </div>
            </div>

            <x-admin.form.button
                type="button"
                variant="solid"
                size="sm"
                class="cursor-pointer"
                @click="addLauncherUpdate({{ $item }})"
            >
                <span class="text-base leading-none">+</span>
                افزودن آپدیت
            </x-admin.form.button>
        </div>

        <div class="space-y-3 p-4">
            <template x-if="{{ $item }}.launcher_updates.length === 0">
                <div
                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-xs text-slate-500">
                    هنوز آپدیتی برای این نسخه تعریف نشده است.
                </div>
            </template>

            <template x-for="(update, updateIndex) in {{ $item }}.launcher_updates" :key="update.__key">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-3 py-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span
                                class="flex h-7 min-w-7 items-center justify-center rounded-full bg-orange-50 px-2 text-[11px] font-bold text-orange-700"
                                x-text="updateIndex + 1"></span>
                            <div class="min-w-0">
                                <div class="truncate text-xs font-bold text-slate-800"
                                     x-text="update.title || 'آپدیت بدون عنوان'"></div>
                                <div class="mt-0.5 text-[11px] text-slate-500"
                                     x-text="update.version ? ('نسخه ' + update.version) : 'نسخه مشخص نشده'"></div>
                            </div>
                        </div>

                        <x-admin.form.button
                            type="button"
                            variant="danger"
                            size="sm"
                            class="cursor-pointer"
                            @click="{{ $item }}.launcher_updates.splice(updateIndex, 1)"
                        >
                            حذف آپدیت
                        </x-admin.form.button>
                    </div>

                    <div class="p-3">
                        <input
                            type="hidden"
                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][id]`"
                            x-model="update.id"
                        >

                        <div class="grid grid-cols-12 gap-4">
                            <x-admin.field label="نسخه" class="col-span-12 md:col-span-3">
                                <x-admin.form.input
                                    class="direction-ltr"
                                    placeholder="1.0.1"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][version]`"
                                    x-model="update.version"
                                />
                            </x-admin.field>

                            <x-admin.field label="عنوان آپدیت" class="col-span-12 md:col-span-5">
                                <x-admin.form.input
                                    placeholder="عنوان آپدیت"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][title]`"
                                    x-model="update.title"
                                />
                            </x-admin.field>

                            <x-admin.field label="تاریخ انتشار" class="col-span-12 md:col-span-4">
                                <x-admin.form.input
                                    class="direction-ltr"
                                    placeholder="2026-08-01 12:00:00"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][published_at]`"
                                    x-model="update.published_at"
                                />
                            </x-admin.field>

                            <x-admin.field label="توضیحات" class="col-span-12 md:col-span-6">
                                <x-admin.form.textarea
                                    rows="2"
                                    placeholder="توضیح این نسخه"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][description]`"
                                    x-model="update.description"
                                />
                            </x-admin.field>

                            <x-admin.field label="تغییرات" class="col-span-12 md:col-span-6">
                                <x-admin.form.textarea
                                    rows="2"
                                    placeholder="Changelog"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][changelog]`"
                                    x-model="update.changelog"
                                />
                            </x-admin.field>
                        </div>

                        <div class="mt-4 grid grid-cols-12 gap-3">
                            @foreach([
                                'is_enabled' => ['title' => 'فعال', 'description' => 'این آپدیت در لانچر منتشر شود.'],
                                'is_mandatory' => ['title' => 'اجباری', 'description' => 'کاربر قبل از ادامه باید این آپدیت را نصب کند.'],
                                'has_option_file' => ['title' => 'دارای OPTION FILE', 'description' => 'مستقل از بسته پایه؛ پوشه OPTION FILE باید بعد از استخراج این آپدیت وجود داشته باشد.'],
                            ] as $field => $option)
                                <div class="col-span-12 md:col-span-4">
                                    <div
                                        class="flex h-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3">
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-800">{{ $option['title'] }}</div>
                                            <div
                                                class="mt-1 text-[11px] leading-5 text-slate-500">{{ $option['description'] }}</div>
                                        </div>

                                        <x-admin.form.switch
                                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][{{ $field }}]`"
                                            x-model="update.{{ $field }}"
                                        />
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-3 py-2.5">
                                <div>
                                    <div class="text-xs font-bold text-slate-800">فایل‌های آپدیت</div>
                                    <div class="mt-0.5 text-[11px] text-slate-500">URL، حجم و Hash فایل‌های این آپدیت.
                                    </div>
                                </div>

                                <x-admin.form.button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    class="cursor-pointer"
                                    @click="addLauncherPackageFile(update)"
                                >
                                    + فایل آپدیت
                                </x-admin.form.button>
                            </div>

                            <div class="space-y-2 p-3">
                                <template x-if="update.files.length === 0">
                                    <div
                                        class="rounded-xl border border-dashed border-slate-300 px-3 py-4 text-center text-[11px] text-slate-500">
                                        فایلی اضافه نشده است.
                                    </div>
                                </template>

                                <template x-for="(file, fileIndex) in update.files" :key="file.__key">
                                    <div
                                        class="grid grid-cols-12 items-end gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                                        <input
                                            type="hidden"
                                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][id]`"
                                            x-model="file.id"
                                        >

                                        <x-admin.field label="نام فایل" class="col-span-12 md:col-span-3">
                                            <x-admin.form.input
                                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][file_name]`"
                                                x-model="file.file_name"
                                                placeholder="update.7z"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="URL خصوصی" class="col-span-12 md:col-span-4">
                                            <x-admin.form.input
                                                class="direction-ltr"
                                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][url]`"
                                                x-model="file.url"
                                                placeholder="Private URL"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="حجم (Byte)" class="col-span-6 md:col-span-2">
                                            <x-admin.form.input
                                                type="number"
                                                min="0"
                                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][size_bytes]`"
                                                x-model="file.size_bytes"
                                                placeholder="0"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="SHA-256" class="col-span-6 md:col-span-2">
                                            <x-admin.form.input
                                                class="direction-ltr"
                                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][sha256]`"
                                                x-model="file.sha256"
                                                placeholder="SHA-256"
                                            />
                                        </x-admin.field>

                                        <div class="col-span-12 md:col-span-1">
                                            <x-admin.form.button
                                                type="button"
                                                variant="danger"
                                                size="sm"
                                                block
                                                class="cursor-pointer"
                                                @click="update.files.splice(fileIndex, 1)"
                                            >
                                                ×
                                            </x-admin.form.button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>

    {{-- Mod groups --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div
            class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-4 py-3">
            <div>
                <div class="text-sm font-bold text-slate-900">گروه‌های ماد</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">مادها را برای نمایش و انتخاب بهتر داخل لانچر
                    گروه‌بندی کنید.
                </div>
            </div>

            <x-admin.form.button
                type="button"
                variant="secondary"
                size="sm"
                class="cursor-pointer"
                @click="addLauncherModGroup({{ $item }})"
            >
                + افزودن گروه
            </x-admin.form.button>
        </div>

        <div class="space-y-3 p-4">
            <template x-if="{{ $item }}.launcher_mod_groups.length === 0">
                <div
                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-xs text-slate-500">
                    هنوز گروه مادی تعریف نشده است.
                </div>
            </template>

            <template x-for="(group, groupIndex) in {{ $item }}.launcher_mod_groups" :key="group.__key">
                <div class="grid grid-cols-12 items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 p-3">
                    <input
                        type="hidden"
                        x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][id]`"
                        x-model="group.id"
                    >

                    <x-admin.field label="کلید گروه" hint="مثال: gameplay" class="col-span-12 md:col-span-4">
                        <x-admin.form.input
                            class="direction-ltr"
                            placeholder="gameplay"
                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][key]`"
                            x-model="group.key"
                        />
                    </x-admin.field>

                    <x-admin.field label="عنوان گروه" class="col-span-12 md:col-span-4">
                        <x-admin.form.input
                            placeholder="عنوان گروه"
                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][title]`"
                            x-model="group.title"
                        />
                    </x-admin.field>

                    <div class="col-span-12 md:col-span-3">
                        <div
                            class="flex h-10 items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-3">
                            <span class="text-xs font-bold text-slate-700">انتخاب انحصاری</span>
                            <x-admin.form.switch
                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][is_exclusive]`"
                                x-model="group.is_exclusive"
                            />
                        </div>
                    </div>

                    <div class="col-span-12 md:col-span-1">
                        <x-admin.form.button
                            type="button"
                            variant="danger"
                            size="sm"
                            block
                            class="cursor-pointer"
                            @click="{{ $item }}.launcher_mod_groups.splice(groupIndex, 1)"
                        >
                            حذف
                        </x-admin.form.button>
                    </div>
                </div>
            </template>
        </div>
    </section>

    {{-- Mod manager --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div
            class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-4 py-3">
            <div>
                <div class="text-sm font-bold text-slate-900">مادمنیجر</div>
                <div class="mt-1 text-xs leading-5 text-slate-500">مادهای قابل نصب و مدیریت برای همین نسخه فروش را تعریف
                    کنید.
                </div>
            </div>

            <x-admin.form.button
                type="button"
                variant="solid"
                size="sm"
                class="cursor-pointer"
                @click="addLauncherMod({{ $item }})"
            >
                + افزودن ماد
            </x-admin.form.button>
        </div>

        <div class="space-y-3 p-4">
            <template x-if="{{ $item }}.launcher_mods.length === 0">
                <div
                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-xs text-slate-500">
                    هنوز مادی برای این نسخه تعریف نشده است.
                </div>
            </template>

            <template x-for="(mod, modIndex) in {{ $item }}.launcher_mods" :key="mod.__key">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/60">
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-3 py-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span
                                class="flex h-7 min-w-7 items-center justify-center rounded-full bg-orange-50 px-2 text-[11px] font-bold text-orange-700"
                                x-text="modIndex + 1"></span>
                            <div class="min-w-0">
                                <div class="truncate text-xs font-bold text-slate-800"
                                     x-text="mod.name || 'ماد بدون نام'"></div>
                                <div class="direction-ltr mt-0.5 truncate text-left text-[11px] text-slate-500"
                                     x-text="mod.slug || 'slug تنظیم نشده'"></div>
                            </div>
                        </div>

                        <x-admin.form.button
                            type="button"
                            variant="danger"
                            size="sm"
                            class="cursor-pointer"
                            @click="{{ $item }}.launcher_mods.splice(modIndex, 1)"
                        >
                            حذف ماد
                        </x-admin.form.button>
                    </div>

                    <div class="p-3">
                        <input
                            type="hidden"
                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][id]`"
                            x-model="mod.id"
                        >

                        <div class="grid grid-cols-12 gap-4">
                            <x-admin.field label="نام ماد" class="col-span-12 md:col-span-4">
                                <x-admin.form.input
                                    placeholder="نام ماد"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][name]`"
                                    x-model="mod.name"
                                />
                            </x-admin.field>

                            <x-admin.field label="Slug" class="col-span-12 md:col-span-2">
                                <x-admin.form.input
                                    class="direction-ltr"
                                    placeholder="mod-slug"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][slug]`"
                                    x-model="mod.slug"
                                />
                            </x-admin.field>

                            <x-admin.field label="نسخه" class="col-span-6 md:col-span-2">
                                <x-admin.form.input
                                    class="direction-ltr"
                                    placeholder="1.0.0"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][version]`"
                                    x-model="mod.version"
                                />
                            </x-admin.field>

                            <x-admin.field label="کلید گروه" class="col-span-6 md:col-span-2">
                                <x-admin.form.input
                                    class="direction-ltr"
                                    placeholder="gameplay"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][group_key]`"
                                    x-model="mod.group_key"
                                />
                            </x-admin.field>

                            <x-admin.field label="اولویت" class="col-span-12 md:col-span-2">
                                <x-admin.form.input
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][priority]`"
                                    x-model="mod.priority"
                                />
                            </x-admin.field>

                            <x-admin.field label="تاریخ انتشار" class="col-span-12 md:col-span-3">
                                <x-admin.form.input
                                    class="direction-ltr"
                                    placeholder="2026-08-01 12:00:00"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][published_at]`"
                                    x-model="mod.published_at"
                                />
                            </x-admin.field>

                            <x-admin.field label="شناسه تصویر" class="col-span-12 md:col-span-3">
                                <x-admin.form.input
                                    type="number"
                                    min="1"
                                    placeholder="Media ID"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][image_media_id]`"
                                    x-model="mod.image_media_id"
                                />
                            </x-admin.field>

                            <x-admin.field label="نسخه‌های Base سازگار" hint="با کاما جدا کنید."
                                           class="col-span-12 md:col-span-3">
                                <x-admin.form.input
                                    class="direction-ltr"
                                    placeholder="1.0.0,1.1.0"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][compatible_base_versions_text]`"
                                    x-model="mod.compatible_base_versions_text"
                                />
                            </x-admin.field>

                            <x-admin.field label="نسخه‌های Update سازگار" hint="با کاما جدا کنید."
                                           class="col-span-12 md:col-span-3">
                                <x-admin.form.input
                                    class="direction-ltr"
                                    placeholder="1.0.1,1.0.2"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][compatible_update_versions_text]`"
                                    x-model="mod.compatible_update_versions_text"
                                />
                            </x-admin.field>

                            <x-admin.field label="توضیح کوتاه" class="col-span-12 md:col-span-5">
                                <x-admin.form.textarea
                                    rows="2"
                                    placeholder="توضیح کوتاه ماد"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][short_description]`"
                                    x-model="mod.short_description"
                                />
                            </x-admin.field>

                            <x-admin.field label="توضیحات کامل" class="col-span-12 md:col-span-7">
                                <x-admin.form.textarea
                                    rows="2"
                                    placeholder="توضیحات کامل ماد"
                                    x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][description]`"
                                    x-model="mod.description"
                                />
                            </x-admin.field>
                        </div>

                        <div class="mt-4 grid grid-cols-12 gap-3">
                            @foreach([
                                'is_enabled' => ['title' => 'منتشر شود', 'description' => 'این ماد در لانچر قابل مشاهده باشد.'],
                                'is_required' => ['title' => 'ماد اجباری', 'description' => 'کاربر امکان غیرفعال کردن این ماد را نداشته باشد.'],
                            ] as $field => $option)
                                <div class="col-span-12 md:col-span-6">
                                    <div
                                        class="flex h-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3">
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-800">{{ $option['title'] }}</div>
                                            <div
                                                class="mt-1 text-[11px] leading-5 text-slate-500">{{ $option['description'] }}</div>
                                        </div>

                                        <x-admin.form.switch
                                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][{{ $field }}]`"
                                            x-model="mod.{{ $field }}"
                                        />
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-3 py-2.5">
                                <div>
                                    <div class="text-xs font-bold text-slate-800">فایل‌های ماد</div>
                                    <div class="mt-0.5 text-[11px] text-slate-500">فایل‌های دانلودی مربوط به این ماد.
                                    </div>
                                </div>

                                <x-admin.form.button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    class="cursor-pointer"
                                    @click="addLauncherPackageFile(mod)"
                                >
                                    + فایل ماد
                                </x-admin.form.button>
                            </div>

                            <div class="space-y-2 p-3">
                                <template x-if="mod.files.length === 0">
                                    <div
                                        class="rounded-xl border border-dashed border-slate-300 px-3 py-4 text-center text-[11px] text-slate-500">
                                        فایلی اضافه نشده است.
                                    </div>
                                </template>

                                <template x-for="(file, fileIndex) in mod.files" :key="file.__key">
                                    <div
                                        class="grid grid-cols-12 items-end gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                                        <input
                                            type="hidden"
                                            x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][id]`"
                                            x-model="file.id"
                                        >

                                        <x-admin.field label="نام فایل" class="col-span-12 md:col-span-3">
                                            <x-admin.form.input
                                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][file_name]`"
                                                x-model="file.file_name"
                                                placeholder="mod.7z"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="URL خصوصی" class="col-span-12 md:col-span-4">
                                            <x-admin.form.input
                                                class="direction-ltr"
                                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][url]`"
                                                x-model="file.url"
                                                placeholder="Private URL"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="حجم (Byte)" class="col-span-6 md:col-span-2">
                                            <x-admin.form.input
                                                type="number"
                                                min="0"
                                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][size_bytes]`"
                                                x-model="file.size_bytes"
                                                placeholder="0"
                                            />
                                        </x-admin.field>

                                        <x-admin.field label="SHA-256" class="col-span-6 md:col-span-2">
                                            <x-admin.form.input
                                                class="direction-ltr"
                                                x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][sha256]`"
                                                x-model="file.sha256"
                                                placeholder="SHA-256"
                                            />
                                        </x-admin.field>

                                        <div class="col-span-12 md:col-span-1">
                                            <x-admin.form.button
                                                type="button"
                                                variant="danger"
                                                size="sm"
                                                block
                                                class="cursor-pointer"
                                                @click="mod.files.splice(fileIndex, 1)"
                                            >
                                                ×
                                            </x-admin.form.button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>
</div>
