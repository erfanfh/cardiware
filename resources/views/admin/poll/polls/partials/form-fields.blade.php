@php
    use App\Domain\Poll\Enums\PollAudienceEnum;
    use App\Domain\Poll\Enums\PollResultsVisibilityEnum;
    use App\Domain\Poll\Enums\PollStatusEnum;

    $poll = $poll ?? null;
    $optionRows = old('options', $poll?->options?->map(fn ($option) => [
        'id' => $option->id,
        'label' => $option->label,
        'description' => $option->description,
        'color' => $option->color ?: '#f97316',
        'is_active' => $option->is_active,
        'responses_count' => $option->responses_count,
    ])->values()->all() ?? [
        ['id' => null, 'label' => '', 'description' => '', 'color' => '#f97316', 'is_active' => true, 'responses_count' => 0],
        ['id' => null, 'label' => '', 'description' => '', 'color' => '#0ea5e9', 'is_active' => true, 'responses_count' => 0],
    ]);
@endphp

<x-admin.form.validation-errors class="mb-6"/>

<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="انتشار" :collapsible="false">
            <div class="space-y-4">
                <x-admin.field label="وضعیت">
                    <x-admin.form.select name="status"
                                         :value="old('status', $poll?->status?->value ?? PollStatusEnum::DRAFT->value)"
                                         :options="collect(PollStatusEnum::cases())->mapWithKeys(fn ($item) => [$item->value => $item->label()])->all()"/>
                </x-admin.field>
                @if($poll && $poll->status === PollStatusEnum::PUBLISHED)
                    <a href="{{ route('polls.show', $poll->slug) }}" target="_blank"
                       class="flex w-full items-center justify-center rounded-2xl bg-brand-50 px-3 py-2 text-xs font-black text-brand-700 transition hover:bg-brand-100">مشاهده
                        صفحه عمومی</a>
                @endif
                @if($poll)
                    <a href="{{ route('admin.polls.analytics', $poll) }}"
                       class="flex w-full items-center justify-center rounded-2xl bg-slate-900 px-3 py-2 text-xs font-black text-white transition hover:bg-slate-800">داشبورد
                        آمار و نتایج</a>
                @endif
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="دسترسی و نتیجه">
            <div class="space-y-4">
                <x-admin.field label="شرکت‌کنندگان مجاز">
                    <x-admin.form.select name="audience"
                                         :value="old('audience', $poll?->audience?->value ?? PollAudienceEnum::EVERYONE->value)"
                                         :options="collect(PollAudienceEnum::cases())->mapWithKeys(fn ($item) => [$item->value => $item->label()])->all()"/>
                </x-admin.field>
                <x-admin.field label="نمایش نتیجه به کاربر">
                    <x-admin.form.select name="results_visibility"
                                         :value="old('results_visibility', $poll?->results_visibility?->value ?? PollResultsVisibilityEnum::AFTER_VOTE->value)"
                                         :options="collect(PollResultsVisibilityEnum::cases())->mapWithKeys(fn ($item) => [$item->value => $item->label()])->all()"/>
                </x-admin.field>
                <div class="flex items-center justify-between gap-3"><span class="text-xs font-bold text-slate-600">نمایش تعداد پاسخ‌ها</span>
                    <x-admin.form.switch name="show_response_count"
                                         :checked="old('show_response_count', $poll?->show_response_count ?? true)"/>
                </div>
            </div>
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.wp-title-slug
        titleName="title" slugName="slug"
        :titleValue="$poll?->title ?? ''" :slugValue="$poll?->slug ?? ''"
        :basePath="url('/polls').'/'"
        :scope="\App\Domain\Slug\Enums\SlugScopeEnum::POLL"
        :currentId="$poll?->id"
        :itemUrl="$poll?->status === PollStatusEnum::PUBLISHED ? route('polls.show', $poll->slug) : null"
    />

    <x-admin.metabox title="صورت سؤال">
        <x-admin.form.editor name="question" :value="old('question', $poll?->question)" :height="280"
                             placeholder="صورت سؤال را با جزئیات بنویسید…"/>
    </x-admin.metabox>

    <x-admin.metabox title="توضیحات تکمیلی">
        <x-admin.form.editor name="description" :value="old('description', $poll?->description)" :height="220"
                             placeholder="توضیحات، زمینه یا راهنمای پاسخ‌گویی…"/>
    </x-admin.metabox>

    <x-admin.metabox title="زمان‌بندی">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin.field label="شروع دریافت رأی" hint="خالی یعنی بلافاصله پس از انتشار.">
                <x-admin.form.date-picker name="starts_at"
                                          :value="old('starts_at', $poll?->starts_at ? jalali_date($poll->starts_at, 'Y/m/d H:i') : '')"/>
            </x-admin.field>
            <x-admin.field label="پایان دریافت رأی" hint="خالی یعنی بدون تاریخ انقضا.">
                <x-admin.form.date-picker name="ends_at"
                                          :value="old('ends_at', $poll?->ends_at ? jalali_date($poll->ends_at, 'Y/m/d H:i') : '')"/>
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="گزینه‌ها">
        <div
            x-data="{
                rows: @js(array_values($optionRows)),
                palette: ['#f97316','#0ea5e9','#8b5cf6','#10b981','#ef4444','#eab308'],
                add() { this.rows.push({id:null,label:'',description:'',color:this.palette[this.rows.length % this.palette.length],is_active:true,responses_count:0}) },
                remove(index) { if (this.rows.length > 2) this.rows.splice(index, 1) }
            }"
            class="space-y-3"
        >
            <template x-for="(row, index) in rows" :key="row.id || `new-${index}`">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                    <input type="hidden" :name="`options[${index}][id]`" :value="row.id || ''">
                    <input type="hidden" :name="`options[${index}][is_active]`" :value="row.is_active ? 1 : 0">
                    <div class="grid grid-cols-[44px_minmax(0,1fr)_auto] items-start gap-3">
                        <input type="color" :name="`options[${index}][color]`" x-model="row.color"
                               class="h-10 w-11 cursor-pointer rounded-xl border border-slate-200 bg-white p-1">
                        <div class="space-y-2">
                            <input type="text" :name="`options[${index}][label]`" x-model="row.label" required
                                   maxlength="500"
                                   class="h-10 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none focus:border-orange-300 focus:ring-2 focus:ring-orange-200"
                                   :placeholder="`متن گزینه ${index + 1}`">
                            <input type="text" :name="`options[${index}][description]`" x-model="row.description"
                                   maxlength="2000"
                                   class="h-9 w-full rounded-2xl border border-slate-200 bg-white px-3 text-xs text-slate-700 outline-none focus:border-orange-300"
                                   placeholder="توضیح کوتاه گزینه (اختیاری)">
                            <div x-show="row.id" class="flex items-center gap-3 text-[11px] text-slate-500">
                                <button type="button" @click="row.is_active = !row.is_active" class="font-bold"
                                        :class="row.is_active ? 'text-emerald-600' : 'text-rose-600'"
                                        x-text="row.is_active ? 'فعال' : 'غیرفعال'"></button>
                                <span x-text="`${Number(row.responses_count || 0).toLocaleString('fa-IR')} رأی`"></span>
                            </div>
                        </div>
                        <div class="grid gap-1 [&_button]:cursor-pointer">
                            <button type="button"
                                    @click="if(index > 0) { const item = rows.splice(index, 1)[0]; rows.splice(index - 1, 0, item) }"
                                    :disabled="index === 0"
                                    class="flex h-7 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 disabled:opacity-25"
                                    title="انتقال به بالا">↑
                            </button>
                            <button type="button"
                                    @click="if(index < rows.length - 1) { const item = rows.splice(index, 1)[0]; rows.splice(index + 1, 0, item) }"
                                    :disabled="index === rows.length - 1"
                                    class="flex h-7 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 disabled:opacity-25"
                                    title="انتقال به پایین">↓
                            </button>
                            <button type="button" @click="remove(index)" :disabled="rows.length <= 2"
                                    class="flex h-7 w-9 items-center justify-center rounded-lg border border-rose-200 bg-white text-base text-rose-500 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-30"
                                    title="حذف">×
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            <button type="button" @click="add()"
                    class="cursor-pointer w-full rounded-2xl border border-dashed border-brand-300 bg-brand-50/60 px-4 py-3 text-xs font-black text-brand-700 transition hover:bg-brand-50">
                + افزودن گزینه جدید
            </button>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="رفتار رأی‌گیری">
        <div
            x-data="{ multiple: @js((bool) old('allows_multiple_selections', $poll?->allows_multiple_selections ?? false)) }"
            class="space-y-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-3">
                    <div class="flex items-center justify-between gap-3"><span class="text-xs font-bold text-slate-700">امکان انتخاب چند گزینه</span>
                        <x-admin.form.switch name="allows_multiple_selections" x-model="multiple"/>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 p-3">
                    <div class="flex items-center justify-between gap-3"><span class="text-xs font-bold text-slate-700">امکان ویرایش رأی</span>
                        <x-admin.form.switch name="allow_vote_changes"
                                             :checked="old('allow_vote_changes', $poll?->allow_vote_changes ?? false)"/>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 p-3">
                    <div class="flex items-center justify-between gap-3"><span class="text-xs font-bold text-slate-700">چینش تصادفی گزینه‌ها</span>
                        <x-admin.form.switch name="randomize_options"
                                             :checked="old('randomize_options', $poll?->randomize_options ?? false)"/>
                    </div>
                </div>
            </div>
            <div x-show="multiple" x-collapse class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-admin.field label="حداقل تعداد انتخاب">
                    <x-admin.form.input name="min_selections" type="number" min="1" max="100"
                                        :value="old('min_selections', $poll?->min_selections ?? 1)"/>
                </x-admin.field>
                <x-admin.field label="حداکثر تعداد انتخاب">
                    <x-admin.form.input name="max_selections" type="number" min="1" max="100"
                                        :value="old('max_selections', $poll?->max_selections ?? 1)"/>
                </x-admin.field>
            </div>
        </div>
    </x-admin.metabox>
</x-admin.page>
