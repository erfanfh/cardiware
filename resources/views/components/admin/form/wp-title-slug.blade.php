@props([
    'titleName' => 'title',
    'slugName' => 'slug',
    'titleValue' => '',
    'slugValue' => '',
    'basePath' => '/',
    'checkUrl' => route('admin.slugs.check'),
    'scope', // SlugScopeEnum
    'currentId' => null,
    'itemUrl' => null,
])

<div
    x-data="mwWpSlugEditor({
        initialTitle: @js(old($titleName, $titleValue)),
        initialSlug:  @js(old($slugName, $slugValue)),
        titleName: @js($titleName),
        slugName:  @js($slugName),
        basePath:  @js($basePath),
        checkUrl:  @js($checkUrl),
        scope:  @js($scope->value),
        currentId: @js($currentId),
    })"
    class="space-y-2"
>
    <div>
        <label class="sr-only">عنوان</label>
        <input
            name="{{ $titleName }}"
            x-model="title"
            @input="$dispatch('title-changed', title)"
            type="text"
            class="w-full rounded-2xl border border-slate-200 shadow-sm bg-white px-4 py-3 text-xl font-black leading-tight outline-none transition focus:ring-2 focus:ring-orange-200 focus:border-orange-300 placeholder:text-slate-400"
            placeholder="افزودن عنوان"
            autocomplete="off"
        >
    </div>

    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-xs pr-2">
        <div class="text-slate-500 font-semibold">پیوند یکتا:</div>

        <div class="min-w-0 flex items-center direction-ltr">
            <span class="text-slate-600 select-none">{{ $basePath }}</span>

            <template x-if="!editingSlug">
                <a @if($itemUrl) href="{{ $itemUrl }}" target="_blank"
                   @endif class="font-bold text-slate-800 truncate max-w-[520px]" x-text="slug || '—'"></a>
            </template>

            <template x-if="editingSlug">
                <input
                    x-ref="slugInput"
                    type="text"
                    class="h-8 w-[280px] max-w-full rounded-2xl direction-ltr border border-slate-200 bg-white px-2.5 text-xs font-bold outline-none transition focus:ring-2 focus:ring-orange-200 focus:border-orange-300"
                    x-model="slug"
                    autocomplete="off"
                    dir="rtl"
                >
            </template>

            <input type="hidden" name="{{ $slugName }}" x-model="slug">
        </div>

        <div class="flex items-center gap-2">
            <button type="button"
                    class="h-7 px-2 rounded-2xl text-[0.65rem] cursor-pointer border border-slate-200 bg-white hover:bg-slate-100 font-bold text-slate-700 transition"
                    x-show="!editingSlug"
                    x-cloak
                    @click="startEdit()"
            >
                ویرایش
            </button>

            @if($itemUrl)
                <a href="{{ $itemUrl }}"
                   target="_blank"
                   class="h-7 leading-7 px-2 rounded-2xl text-[0.65rem] cursor-pointer border border-orange-200 bg-orange-50 hover:bg-orange-100 font-bold text-orange-700 transition"
                   x-show="!editingSlug"
                   x-cloak
                >
                    مشاهده
                </a>
            @endif

            <button type="button"
                    class="h-7 px-2 rounded-2xl text-[0.65rem] cursor-pointer border border-slate-200 bg-white hover:bg-slate-100 font-bold text-slate-700 transition"
                    x-show="editingSlug"
                    x-cloak
                    @click="applyEdit()"
            >
                تأیید
            </button>

            <button type="button"
                    class="h-7 px-2 rounded-2xl text-[0.65rem] cursor-pointer border border-slate-200 bg-white hover:bg-slate-100 font-bold text-slate-700 transition"
                    x-show="editingSlug"
                    x-cloak
                    @click="cancelEdit()"
            >
                لغو
            </button>

            <button type="button"
                    class="h-7 px-2 rounded-2xl text-[0.65rem] cursor-pointer border border-orange-200 bg-orange-50 hover:bg-orange-100 font-bold text-orange-700 transition"
                    x-show="editingSlug && manualSlug"
                    x-cloak
                    @click="resetAuto()"
                    title="برگشت به حالت خودکار"
            >
                بازنشانی
            </button>
        </div>

        <div class="w-full flex items-center gap-2 text-[0.68rem]">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-slate-500">
                    <span class="h-1.5 w-1.5 rounded-full"
                          :class="checking ? 'bg-slate-400' : (uniqueOk ? 'bg-emerald-500' : 'bg-rose-500')"></span>
                    <span
                        x-text="checking ? 'در حال بررسی...' : (uniqueOk ? 'اسلاگ قابل استفاده است' : (uniqueMsg || 'اسلاگ تکراری است'))"></span>
                </span>
            </div>

            <template x-if="suggestedSlug && !uniqueOk">
                <button type="button"
                        class="cursor-pointer text-orange-700 font-bold hover:underline"
                        @click="useSuggested()">
                    استفاده از: <span x-text="suggestedSlug"></span>
                </button>
            </template>
        </div>
    </div>
</div>
