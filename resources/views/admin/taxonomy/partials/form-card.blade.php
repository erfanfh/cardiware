@php
    $title = $this->isEditing ? "ویرایش {$taxonomyLabel}" : "افزودن {$taxonomyLabel} جدید";
    $description = $this->isEditing
        ? "اطلاعات {$taxonomyLabel} انتخاب‌شده را ویرایش کنید."
        : "یک {$taxonomyLabel} جدید برای دسته‌بندی بهتر محتوا اضافه کنید.";
@endphp

<x-admin.metabox>
    <x-slot name="title">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-sm font-black text-slate-900">{{ $title }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ $description }}</div>
            </div>

            @if($this->isEditing)
                <button
                    type="button"
                    wire:click="cancelEditing"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                >
                    انصراف
                </button>
            @endif
        </div>
    </x-slot>

    <form wire:submit="save" class="space-y-5">
        <x-admin.validation-errors/>

        <div class="space-y-4">
            <x-admin.field
                label="نام"
                for="taxonomy-term-name"
                hint="نامی که در پنل و بخش‌های مختلف نمایش داده می‌شود."
                :error="$errors->first('name')"
            >
                <x-admin.form.input
                    id="taxonomy-term-name"
                    wire:model.live.debounce.400ms="name"
                    placeholder="مثلاً فوتبال، بازی اکشن، پچ گرافیکی"
                />
            </x-admin.field>

            <x-admin.field
                label="نامک"
                for="taxonomy-term-slug"
                hint="اختیاری است. اگر خالی بماند، به‌صورت خودکار ساخته می‌شود."
                :error="$errors->first('slug')"
            >
                <x-admin.form.input
                    id="taxonomy-term-slug"
                    dir="ltr"
                    class="text-left"
                    wire:model.live.debounce.400ms="slug"
                    placeholder="game-mods"
                />
            </x-admin.field>

            <x-admin.field
                label="توضیح"
                for="taxonomy-term-description"
                hint="در همه بخش‌های سایت نمایش داده نمی‌شود، اما برای مدیریت بهتر مفید است."
                :error="$errors->first('description')"
            >
                <x-admin.form.textarea
                    id="taxonomy-term-description"
                    rows="5"
                    wire:model.live.debounce.400ms="description"
                    placeholder="توضیح کوتاهی درباره این مورد بنویسید..."
                />
            </x-admin.field>
        </div>

        <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-soft transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg wire:loading wire:target="save" class="ml-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25" stroke-width="4"></circle>
                    <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                          stroke-linecap="round"></path>
                </svg>

                <span>
                    {{ $this->isEditing ? 'ذخیره تغییرات' : "افزودن {$taxonomyLabel}" }}
                </span>
            </button>

            @if($this->isEditing)
                <button
                    type="button"
                    wire:click="resetForm"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                >
                    پاک کردن فرم
                </button>
            @endif
        </div>
    </form>
</x-admin.metabox>
