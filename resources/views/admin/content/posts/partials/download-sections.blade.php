@php
    $downloadSections = old('download_sections', $downloadSections ?? []);
@endphp

<div class="space-y-4">
    <div class="rounded-2xl border border-orange-100 bg-orange-50/70 px-4 py-3 text-sm leading-7 text-slate-700">
        در این بخش می‌توانید سکشن‌های دانلود پست را مدیریت کنید. هر سکشن می‌تواند شامل چند لینک یا متن
        مثل رمز فایل، توضیح نصب یا پارت‌های دانلود باشد.
    </div>

    <x-admin.form.repeater
        name="download_sections"
        :items="$downloadSections"
        :defaultItem="[
                'id' => null,
                'title' => '',
                'description' => '',
                'password' => '',
                'entries' => [],
            ]"
        title="سکشن‌های دانلود"
        addText="افزودن سکشن دانلود"
        emptyText="هنوز هیچ سکشن دانلودی اضافه نشده است."
        headingKey="title"
        headingPlaceholder="سکشن دانلود جدید"
    >
        <div class="space-y-4">
            <input
                type="hidden"
                x-bind:name="field(i, 'id')"
                x-model="it.id"
            >

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">
                        عنوان سکشن
                    </label>

                    <x-admin.form.input
                        x-bind:name="field(i, 'title')"
                        x-model="it.title"
                        placeholder="مثلاً آپدیت 1.4 یا بخش اصلی پچ"
                    />
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">
                        رمز فایل
                    </label>

                    <x-admin.form.input
                        x-bind:name="field(i, 'password')"
                        x-model="it.password"
                        placeholder="مثلاً PES_PATCH_2025"
                    />
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700">
                    توضیح
                </label>

                <x-admin.form.textarea
                    rows="3"
                    x-bind:name="field(i, 'description')"
                    x-model="it.description"
                    placeholder="توضیح کوتاه درباره این بخش دانلود، در صورت نیاز"
                />
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4"
                x-data
                x-init="
                        if (!Array.isArray(it.entries)) {
                            it.entries = [];
                        }
                    "
            >
                <div
                    x-data="mwRepeater({
                            items: it.entries,
                            name: `${field(i, 'entries')}`,
                            defaultItem: {
                                id: null,
                                type: 'link',
                                title: '',
                                content: '',
                            },
                            sortable: true,
                        })"
                    class="space-y-3"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-xs text-slate-500">
                            آیتم‌های این سکشن
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="text-xs text-slate-500 hover:text-slate-700"
                                @click="expandAll()"
                            >
                                باز کردن همه
                            </button>

                            <button
                                type="button"
                                class="text-xs text-slate-500 hover:text-slate-700"
                                @click="collapseAll()"
                            >
                                بستن همه
                            </button>

                            <x-admin.form.button
                                type="button"
                                variant="soft"
                                size="sm"
                                @click="add()"
                            >
                                + افزودن آیتم
                            </x-admin.form.button>
                        </div>
                    </div>

                    <template x-if="items.length === 0">
                        <div
                            class="rounded-xl border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500">
                            هنوز آیتمی برای این سکشن اضافه نشده است.
                        </div>
                    </template>

                    <div x-ref="list" class="space-y-3">
                        <template x-for="(entry, entryIndex) in items" :key="`${entry.__key}-${__renderNonce}`">
                            <div
                                data-repeater-item
                                class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                            >
                                <div
                                    class="flex items-center justify-between gap-3 p-4 cursor-pointer"
                                    @click="toggle(entryIndex)"
                                >
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div
                                            class="drag-handle cursor-move text-slate-400 hover:text-slate-600"
                                            @click.stop
                                            title="جابجایی"
                                        >
                                            ☰
                                        </div>

                                        <div class="min-w-0">
                                            <div class="truncate text-xs font-bold text-slate-800 max-w-[420px]">
                                                    <span
                                                        x-text="(entry.title && String(entry.title).trim().length)
                                                            ? entry.title
                                                            : 'آیتم جدید'"
                                                        :class="(!entry.title || !String(entry.title).trim().length)
                                                            ? 'text-slate-400 font-semibold'
                                                            : ''"
                                                    ></span>
                                            </div>

                                            <div
                                                class="truncate text-[11px] text-slate-500 max-w-[420px]"
                                                x-show="!entry._open"
                                                x-cloak
                                            >
                                                    <span
                                                        x-text="entry.type === 'text' ? 'آیتم متنی' : 'لینک دانلود'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <button
                                            type="button"
                                            class="h-9 w-9 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100"
                                            @click.stop="remove(entryIndex)"
                                            title="حذف"
                                        >
                                            ✕
                                        </button>

                                        <div class="select-none text-slate-400">
                                            <span x-show="entry._open" x-cloak>▾</span>
                                            <span x-show="!entry._open" x-cloak>▸</span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="border-t border-slate-200 p-4"
                                    x-show="entry._open"
                                    x-cloak
                                >
                                    <input
                                        type="hidden"
                                        x-bind:name="`${field(i, 'entries')}[${entryIndex}][id]`"
                                        x-model="entry.id"
                                    >

                                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                                        <div class="space-y-2">
                                            <label class="block text-sm font-bold text-slate-700">
                                                نوع آیتم
                                            </label>

                                            <x-admin.form.select
                                                x-bind:name="`${field(i, 'entries')}[${entryIndex}][type]`"
                                                x-model="entry.type"
                                                :options="[
                                                        ['value' => 'link', 'label' => 'لینک'],
                                                        ['value' => 'text', 'label' => 'متن'],
                                                    ]"
                                            />
                                        </div>

                                        <div class="space-y-2 xl:col-span-2">
                                            <label class="block text-sm font-bold text-slate-700">
                                                عنوان آیتم
                                            </label>

                                            <x-admin.form.input
                                                x-bind:name="`${field(i, 'entries')}[${entryIndex}][title]`"
                                                x-model="entry.title"
                                                placeholder="مثلاً لینک مستقیم، Part 01 یا رمز نصب"
                                            />
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-2">
                                        <label class="block text-sm font-bold text-slate-700">
                                            <span x-text="entry.type === 'text' ? 'متن' : 'آدرس لینک'"></span>
                                        </label>

                                        <template x-if="entry.type === 'text'">
                                            <div>
                                                <x-admin.form.textarea
                                                    rows="3"
                                                    x-bind:name="`${field(i, 'entries')}[${entryIndex}][content]`"
                                                    x-model="entry.content"
                                                    placeholder="مثلاً رمز فایل یا توضیح متنی"
                                                />
                                            </div>
                                        </template>

                                        <template x-if="entry.type !== 'text'">
                                            <div>
                                                <x-admin.form.input
                                                    type="url"
                                                    x-bind:name="`${field(i, 'entries')}[${entryIndex}][content]`"
                                                    x-model="entry.content"
                                                    placeholder="https://example.com/file.zip"
                                                    dir="ltr"
                                                />
                                            </div>
                                        </template>
                                    </div>

                                    <input
                                        type="hidden"
                                        x-bind:name="`${field(i, 'entries')}[${entryIndex}][sort_order]`"
                                        x-model="entry.sort_order"
                                    >
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </x-admin.form.repeater>
</div>
