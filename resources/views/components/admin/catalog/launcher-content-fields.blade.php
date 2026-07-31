@props(['mode' => 'variable'])

@php
    $item = $mode === 'simple' ? 'saleItems[0]' : 'item';
    $index = $mode === 'simple' ? '0' : 'i';
    $indexToken = $mode === 'simple' ? '${0}' : '${i}';
@endphp

<div class="space-y-5" x-show="{{ $item }}">
    <section class="rounded-2xl border border-orange-200 bg-orange-50/30 p-4">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-slate-900">انتشار در MW Launcher</h4>
                <p class="mt-1 text-xs text-slate-500">تنظیمات این نسخه فروش مستقل از نسخه‌های دیگر است.</p>
            </div>
            <label class="flex items-center gap-2 text-xs font-bold">
                <input type="hidden"
                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_enabled]`" value="0">
                <input type="checkbox"
                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_enabled]`"
                       value="1" x-model="{{ $item }}.launcher_enabled">
                نمایش در لانچر
            </label>
        </div>

        <div class="grid grid-cols-12 gap-3">
            <div class="col-span-12 md:col-span-6">
                <x-admin.field label="عنوان در لانچر">
                    <x-admin.form.input
                        x-bind:name="`sale_items[{{ $indexToken }}][launcher_title]`"
                        x-model="{{ $item }}.launcher_title"/>
                </x-admin.field>
            </div>
            <div class="col-span-12 md:col-span-3">
                <x-admin.field label="شناسه تصویر">
                    <x-admin.form.input type="number"
                        x-bind:name="`sale_items[{{ $indexToken }}][launcher_cover_media_id]`"
                        x-model="{{ $item }}.launcher_cover_media_id"/>
                </x-admin.field>
            </div>
            <div class="col-span-12 md:col-span-3">
                <x-admin.field label="ترتیب">
                    <x-admin.form.input type="number" min="0"
                        x-bind:name="`sale_items[{{ $indexToken }}][launcher_sort_order]`"
                        x-model="{{ $item }}.launcher_sort_order"/>
                </x-admin.field>
            </div>
            <div class="col-span-12">
                <x-admin.field label="توضیح کوتاه لانچر">
                    <x-admin.form.textarea rows="2"
                        x-bind:name="`sale_items[{{ $indexToken }}][launcher_excerpt]`"
                        x-model="{{ $item }}.launcher_excerpt"/>
                </x-admin.field>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 p-4">
        <div class="mb-3 flex items-center justify-between">
            <div class="font-bold text-slate-900">آپدیت‌های محصول</div>
            <button type="button" @click="addLauncherUpdate({{ $item }})"
                    class="rounded-xl bg-orange-500 px-3 py-2 text-xs font-bold text-white">+ افزودن آپدیت</button>
        </div>
        <div class="space-y-3">
            <template x-for="(update, updateIndex) in {{ $item }}.launcher_updates" :key="update.__key">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <input type="hidden" x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][id]`" x-model="update.id">
                    <div class="grid grid-cols-12 gap-2">
                        <input class="col-span-3 rounded-lg border p-2 direction-ltr" placeholder="نسخه"
                               x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][version]`" x-model="update.version">
                        <input class="col-span-5 rounded-lg border p-2" placeholder="عنوان آپدیت"
                               x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][title]`" x-model="update.title">
                        <input class="col-span-3 rounded-lg border p-2 direction-ltr" placeholder="2026-08-01 12:00:00"
                               x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][published_at]`" x-model="update.published_at">
                        <button type="button" @click="{{ $item }}.launcher_updates.splice(updateIndex, 1)"
                                class="col-span-1 rounded-lg bg-rose-50 text-rose-600">حذف</button>
                        <textarea class="col-span-6 rounded-lg border p-2" rows="2" placeholder="توضیحات"
                                  x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][description]`" x-model="update.description"></textarea>
                        <textarea class="col-span-6 rounded-lg border p-2" rows="2" placeholder="تغییرات"
                                  x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][changelog]`" x-model="update.changelog"></textarea>
                    </div>
                    <div class="mt-2 flex gap-4 text-xs">
                        @foreach(['is_enabled' => 'فعال', 'is_mandatory' => 'اجباری', 'has_option_file' => 'دارای OPTION FILE'] as $field => $label)
                            <label><input type="hidden" x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][{{ $field }}]`" value="0">
                                <input type="checkbox" value="1"
                                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][{{ $field }}]`"
                                       x-model="update.{{ $field }}"> {{ $label }}</label>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <button type="button" @click="addLauncherPackageFile(update)"
                                class="rounded-lg border px-2 py-1 text-xs">+ فایل آپدیت</button>
                        <template x-for="(file, fileIndex) in update.files" :key="file.__key">
                            <div class="mt-2 grid grid-cols-12 gap-2">
                                <input type="hidden" x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][id]`" x-model="file.id">
                                <input class="col-span-2 rounded border p-2" placeholder="نام فایل"
                                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][file_name]`" x-model="file.file_name">
                                <input class="col-span-4 rounded border p-2 direction-ltr" placeholder="URL خصوصی"
                                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][url]`" x-model="file.url">
                                <input class="col-span-2 rounded border p-2" type="number" placeholder="Size bytes"
                                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][size_bytes]`" x-model="file.size_bytes">
                                <input class="col-span-3 rounded border p-2 direction-ltr" placeholder="SHA-256"
                                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_updates][${updateIndex}][files][${fileIndex}][sha256]`" x-model="file.sha256">
                                <button type="button" @click="update.files.splice(fileIndex,1)" class="rounded bg-rose-50 text-rose-600">×</button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 p-4">
        <div class="mb-3 flex items-center justify-between">
            <div class="font-bold text-slate-900">گروه‌های ماد</div>
            <button type="button" @click="addLauncherModGroup({{ $item }})" class="rounded-xl border px-3 py-2 text-xs">+ گروه</button>
        </div>
        <template x-for="(group, groupIndex) in {{ $item }}.launcher_mod_groups" :key="group.__key">
            <div class="mb-2 grid grid-cols-12 gap-2">
                <input type="hidden" x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][id]`" x-model="group.id">
                <input class="col-span-4 rounded border p-2 direction-ltr" placeholder="کلید گروه؛ gameplay"
                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][key]`" x-model="group.key">
                <input class="col-span-4 rounded border p-2" placeholder="عنوان گروه"
                       x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][title]`" x-model="group.title">
                <label class="col-span-3 p-2 text-xs"><input type="hidden" x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][is_exclusive]`" value="0">
                    <input type="checkbox" value="1" x-bind:name="`sale_items[{{ $indexToken }}][launcher_mod_groups][${groupIndex}][is_exclusive]`" x-model="group.is_exclusive"> انحصاری</label>
                <button type="button" @click="{{ $item }}.launcher_mod_groups.splice(groupIndex,1)" class="rounded bg-rose-50 text-rose-600">حذف</button>
            </div>
        </template>
    </section>

    <section class="rounded-2xl border border-slate-200 p-4">
        <div class="mb-3 flex items-center justify-between">
            <div class="font-bold text-slate-900">مادمنیجر</div>
            <button type="button" @click="addLauncherMod({{ $item }})"
                    class="rounded-xl bg-orange-500 px-3 py-2 text-xs font-bold text-white">+ افزودن ماد</button>
        </div>
        <template x-for="(mod, modIndex) in {{ $item }}.launcher_mods" :key="mod.__key">
            <div class="mb-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                <input type="hidden" x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][id]`" x-model="mod.id">
                <div class="grid grid-cols-12 gap-2">
                    <input class="col-span-3 rounded border p-2" placeholder="نام ماد"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][name]`" x-model="mod.name">
                    <input class="col-span-2 rounded border p-2 direction-ltr" placeholder="slug"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][slug]`" x-model="mod.slug">
                    <input class="col-span-2 rounded border p-2 direction-ltr" placeholder="version"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][version]`" x-model="mod.version">
                    <input class="col-span-2 rounded border p-2 direction-ltr" placeholder="group key"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][group_key]`" x-model="mod.group_key">
                    <input class="col-span-2 rounded border p-2" type="number" placeholder="اولویت"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][priority]`" x-model="mod.priority">
                    <button type="button" @click="{{ $item }}.launcher_mods.splice(modIndex,1)" class="rounded bg-rose-50 text-rose-600">حذف</button>
                    <input class="col-span-3 rounded border p-2 direction-ltr" placeholder="تاریخ انتشار: 2026-08-01 12:00:00"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][published_at]`" x-model="mod.published_at">
                    <input class="col-span-3 rounded border p-2" placeholder="شناسه تصویر"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][image_media_id]`" x-model="mod.image_media_id">
                    <input class="col-span-3 rounded border p-2 direction-ltr" placeholder="نسخه‌های Base: 1.0.0,1.1.0"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][compatible_base_versions_text]`" x-model="mod.compatible_base_versions_text">
                    <input class="col-span-3 rounded border p-2 direction-ltr" placeholder="نسخه‌های Update"
                           x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][compatible_update_versions_text]`" x-model="mod.compatible_update_versions_text">
                    <textarea class="col-span-5 rounded border p-2" rows="2" placeholder="توضیح کوتاه"
                              x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][short_description]`" x-model="mod.short_description"></textarea>
                    <textarea class="col-span-7 rounded border p-2" rows="2" placeholder="توضیحات کامل"
                              x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][description]`" x-model="mod.description"></textarea>
                </div>
                <div class="mt-2 flex gap-4 text-xs">
                    @foreach(['is_enabled' => 'منتشر شود', 'is_required' => 'اجباری'] as $field => $label)
                        <label><input type="hidden" x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][{{ $field }}]`" value="0">
                            <input type="checkbox" value="1" x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][{{ $field }}]`" x-model="mod.{{ $field }}"> {{ $label }}</label>
                    @endforeach
                </div>
                <div class="mt-3">
                    <button type="button" @click="addLauncherPackageFile(mod)" class="rounded-lg border px-2 py-1 text-xs">+ فایل ماد</button>
                    <template x-for="(file, fileIndex) in mod.files" :key="file.__key">
                        <div class="mt-2 grid grid-cols-12 gap-2">
                            <input type="hidden" x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][id]`" x-model="file.id">
                            <input class="col-span-2 rounded border p-2" placeholder="نام فایل"
                                   x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][file_name]`" x-model="file.file_name">
                            <input class="col-span-4 rounded border p-2 direction-ltr" placeholder="URL خصوصی"
                                   x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][url]`" x-model="file.url">
                            <input class="col-span-2 rounded border p-2" type="number" placeholder="Size bytes"
                                   x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][size_bytes]`" x-model="file.size_bytes">
                            <input class="col-span-3 rounded border p-2 direction-ltr" placeholder="SHA-256"
                                   x-bind:name="`sale_items[{{ $indexToken }}][launcher_mods][${modIndex}][files][${fileIndex}][sha256]`" x-model="file.sha256">
                            <button type="button" @click="mod.files.splice(fileIndex,1)" class="rounded bg-rose-50 text-rose-600">×</button>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </section>
</div>
