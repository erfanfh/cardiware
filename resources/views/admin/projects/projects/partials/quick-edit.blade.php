<x-admin.list.quick-edit-row :row-id="$project->id" colspan="7">
    <div class="space-y-5">
        <form
            method="POST"
            action="{{ route('admin.projects.quick-update', $project) }}"
            class="space-y-5"
            x-on:submit.prevent="submitQuickEdit($event.target, {{ $project->id }})"
        >
            @csrf
            @method('PATCH')

            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-800">
                            ویرایش سریع پروژه
                        </h3>

                        <x-admin.badge color="blue">
                            شناسه: {{ $project->id }}
                        </x-admin.badge>
                    </div>

                    <p class="text-xs text-slate-500">
                        اطلاعات اصلی پروژه را سریع و بدون خروج از لیست ویرایش کن.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isSaving({{ $project->id }})"
                    >
                        <span x-show="!isSaving({{ $project->id }})">ذخیره تغییرات</span>
                        <span x-show="isSaving({{ $project->id }})" x-cloak>در حال ذخیره...</span>
                    </button>

                    <button
                        type="button"
                        x-on:click="close()"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        انصراف
                    </button>
                </div>
            </div>

            <div
                x-show="hasErrors({{ $project->id }})"
                x-cloak
                class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
            >
                <div class="font-bold">خطا در ذخیره‌سازی</div>

                <template x-if="fieldError({{ $project->id }}, 'general')">
                    <p class="mt-2" x-text="fieldError({{ $project->id }}, 'general')"></p>
                </template>

                <ul class="mt-2 list-disc space-y-1 pr-5">
                    <template x-if="fieldError({{ $project->id }}, 'title')">
                        <li x-text="fieldError({{ $project->id }}, 'title')"></li>
                    </template>

                    <template x-if="fieldError({{ $project->id }}, 'status')">
                        <li x-text="fieldError({{ $project->id }}, 'status')"></li>
                    </template>

                    <template x-if="fieldError({{ $project->id }}, 'progress_percent')">
                        <li x-text="fieldError({{ $project->id }}, 'progress_percent')"></li>
                    </template>

                    <template x-if="fieldError({{ $project->id }}, 'tags')">
                        <li x-text="fieldError({{ $project->id }}, 'tags')"></li>
                    </template>
                </ul>
            </div>

            <x-admin.form.error-box/>

            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 lg:col-span-5">
                    <x-admin.field label="عنوان پروژه">
                        <x-admin.form.input
                            name="title"
                            :value="old('title', $project->title)"
                        />
                        <p
                            x-show="fieldError({{ $project->id }}, 'title')"
                            x-cloak
                            class="mt-2 text-xs text-rose-600"
                            x-text="fieldError({{ $project->id }}, 'title')"
                        ></p>
                    </x-admin.field>
                </div>

                <div class="col-span-12 sm:col-span-6 lg:col-span-3">
                    <x-admin.field label="وضعیت">
                        <x-admin.form.select
                            name="status"
                            :options="$statuses"
                            :value="old('status', $project->status?->value)"
                        />
                        <p
                            x-show="fieldError({{ $project->id }}, 'status')"
                            x-cloak
                            class="mt-2 text-xs text-rose-600"
                            x-text="fieldError({{ $project->id }}, 'status')"
                        ></p>
                    </x-admin.field>
                </div>

                <div class="col-span-12 sm:col-span-6 lg:col-span-4">
                    <x-admin.field label="درصد پیشرفت">
                        <div class="space-y-3">
                            <x-admin.form.input
                                type="number"
                                name="progress_percent"
                                min="0"
                                max="100"
                                :value="old('progress_percent', $project->progress_percent ?? 0)"
                            />
                            <p
                                x-show="fieldError({{ $project->id }}, 'progress_percent')"
                                x-cloak
                                class="mt-2 text-xs text-rose-600"
                                x-text="fieldError({{ $project->id }}, 'progress_percent')"
                            ></p>

                            <p class="text-xs text-slate-500">
                                عددی بین ۰ تا ۱۰۰ وارد کن.
                            </p>
                        </div>
                    </x-admin.field>
                </div>

                <div class="col-span-12">
                    <x-admin.field label="برچسب‌ها">
                        <x-admin.form.textarea
                            name="tags"
                            rows="4"
                            :value="old('tags', $project->tags?->pluck('title')->implode('، '))"
                            placeholder="مثلاً: لاراول، Livewire، Tailwind"
                        />
                        <p
                            x-show="fieldError({{ $project->id }}, 'tags')"
                            x-cloak
                            class="mt-2 text-xs text-rose-600"
                            x-text="fieldError({{ $project->id }}, 'tags')"
                        ></p>

                        <p class="mt-2 text-xs leading-6 text-slate-500">
                            برچسب‌ها را با ویرگول فارسی یا انگلیسی از هم جدا کن. برچسب‌های جدید در صورت نیاز ساخته
                            می‌شوند.
                        </p>
                    </x-admin.field>
                </div>
            </div>
        </form>
    </div>
</x-admin.list.quick-edit-row>
