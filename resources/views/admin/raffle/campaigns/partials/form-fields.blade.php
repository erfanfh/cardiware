@php
    use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;

    $drawCampaign = $drawCampaign ?? null;
    $structureEditable = !$drawCampaign || (
        in_array($drawCampaign->status, [DrawCampaignStatusEnum::DRAFT, DrawCampaignStatusEnum::SCHEDULED], true)
        && (int)$drawCampaign->issued_tickets_count === 0
    );
@endphp

<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="وضعیت قرعه کشی" :collapsible="false">
            @if($drawCampaign)
                @php
                    $statusColor = match($drawCampaign->status) {
                        DrawCampaignStatusEnum::DRAFT => 'slate',
                        DrawCampaignStatusEnum::SCHEDULED => 'blue',
                        DrawCampaignStatusEnum::OPEN => 'green',
                        DrawCampaignStatusEnum::CLOSED => 'yellow',
                        DrawCampaignStatusEnum::DRAWING => 'brand',
                        DrawCampaignStatusEnum::COMPLETED => 'emerald',
                        DrawCampaignStatusEnum::CANCELLED => 'red',
                    };
                @endphp
                <div class="space-y-3">
                    <x-admin.badge :color="$statusColor" size="md">{{ $drawCampaign->status->label() }}</x-admin.badge>
                    <p class="text-xs leading-7 text-slate-500">
                        تغییر وضعیت از صفحه جزئیات قرعه کشی انجام می‌شود تا Lifecycle به صورت کنترل شده حفظ شود.
                    </p>
                    <x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.show', $drawCampaign) }}" variant="secondary" class="w-full justify-center">
                        مشاهده داشبورد قرعه کشی
                    </x-admin.form.button>
                </div>
            @else
                <x-admin.badge color="slate" size="md">پیش نویس</x-admin.badge>
                <p class="mt-3 text-xs leading-7 text-slate-500">قرعه کشی جدید همیشه به صورت پیش نویس ساخته می‌شود.</p>
            @endif
        </x-admin.metabox>

        <x-admin.metabox title="کاور قرعه کشی" class="overflow-visible">
            <x-admin.form.media-picker
                mode="field"
                name="cover"
                :value="[
                    'id' => old('cover.id', $drawCampaign?->cover_media_file_id),
                    'path' => old('cover.path', $drawCampaign?->coverMediaFile?->url),
                ]"
                layout="vertical"
            />
        </x-admin.metabox>

        <x-admin.metabox title="نمایش نتیجه">
            <x-admin.form.switch
                name="show_winners_publicly"
                label="نمایش عمومی برندگان"
                :checked="old('show_winners_publicly', $drawCampaign?->show_winners_publicly ?? false)"
            />
            <p class="mt-2 text-xs leading-6 text-slate-500">جزئیات Privacy نمایش برنده در فاز فرانت نهایی می‌شود.</p>
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.metabox title="اطلاعات اصلی">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin.field required label="عنوان قرعه کشی">
                <x-admin.form.input name="title" :value="old('title', $drawCampaign?->title)"/>
            </x-admin.field>

            <x-admin.field required label="اسلاگ" hint="فقط حروف انگلیسی، عدد، خط تیره و زیرخط.">
                <x-admin.form.input name="slug" dir="ltr" :value="old('slug', $drawCampaign?->slug)"/>
            </x-admin.field>

            <div class="md:col-span-2">
                <x-admin.field label="توضیح کوتاه">
                    <x-admin.form.textarea name="short_description" rows="3" :value="old('short_description', $drawCampaign?->short_description)"/>
                </x-admin.field>
            </div>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="معرفی قرعه کشی">
        <x-admin.form.editor name="body" :value="old('body', $drawCampaign?->body)"/>
    </x-admin.metabox>

    <x-admin.metabox title="قوانین شرکت">
        <x-admin.form.editor name="rules" :value="old('rules', $drawCampaign?->rules)"/>
    </x-admin.metabox>

    @if($structureEditable)
        <x-admin.metabox title="زمان بندی و ظرفیت">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <x-admin.field label="زمان شروع">
                    <x-admin.form.date-picker name="starts_at" :value="old('starts_at', $drawCampaign?->starts_at ? jalali_date($drawCampaign->starts_at, 'Y/m/d H:i') : '')"/>
                </x-admin.field>
                <x-admin.field label="زمان پایان دریافت بلیط">
                    <x-admin.form.date-picker name="closes_at" :value="old('closes_at', $drawCampaign?->closes_at ? jalali_date($drawCampaign->closes_at, 'Y/m/d H:i') : '')"/>
                </x-admin.field>
                <x-admin.field label="زمان برنامه ریزی شده قرعه کشی">
                    <x-admin.form.date-picker name="draw_at" :value="old('draw_at', $drawCampaign?->draw_at ? jalali_date($drawCampaign->draw_at, 'Y/m/d H:i') : '')"/>
                </x-admin.field>
                <x-admin.field label="ظرفیت کل بلیط" hint="خالی یعنی نامحدود.">
                    <x-admin.form.input name="ticket_limit" type="number" min="1" dir="ltr" :value="old('ticket_limit', $drawCampaign?->ticket_limit)"/>
                </x-admin.field>
                <x-admin.field label="سقف بلیط هر کاربر" hint="خالی یعنی نامحدود.">
                    <x-admin.form.input name="ticket_limit_per_user" type="number" min="1" dir="ltr" :value="old('ticket_limit_per_user', $drawCampaign?->ticket_limit_per_user)"/>
                </x-admin.field>
                <x-admin.field required label="برنده ذخیره پیش فرض برای هر جایزه">
                    <x-admin.form.input name="reserve_winners_per_prize" type="number" min="0" max="100" dir="ltr" :value="old('reserve_winners_per_prize', $drawCampaign?->reserve_winners_per_prize ?? 3)"/>
                </x-admin.field>
                <x-admin.field label="مهلت تایید برنده (ساعت)">
                    <x-admin.form.input name="winner_confirmation_window_hours" type="number" min="1" dir="ltr" :value="old('winner_confirmation_window_hours', $drawCampaign?->winner_confirmation_window_hours ?? 72)"/>
                </x-admin.field>
                <div class="md:col-span-2">
                    <x-admin.form.switch
                        name="allow_multiple_wins_per_user"
                        label="اجازه چند برد برای یک کاربر"
                        :checked="old('allow_multiple_wins_per_user', $drawCampaign?->allow_multiple_wins_per_user ?? false)"
                    />
                    <p class="mt-2 text-xs leading-6 text-slate-500">پیش فرض خاموش است؛ در این حالت یک کاربر در یک اجرای قرعه کشی فقط یک جایزه اصلی خواهد داشت.</p>
                </div>
            </div>
        </x-admin.metabox>

        @include('admin.raffle.campaigns.partials.prizes')
    @else
        <x-admin.metabox title="تنظیمات قفل شده">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-7 text-amber-800">
                با شروع قرعه کشی یا صدور اولین بلیط، ساختار جوایز، ظرفیت، زمان بندی و قوانین برد قفل می‌شوند تا عدالت و قابلیت Audit حفظ شود. محتوای معرفی و قوانین متنی همچنان قابل ویرایش است.
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">ظرفیت کل</div><div class="mt-1 text-sm font-black text-slate-800">{{ $drawCampaign->ticket_limit ? number_format($drawCampaign->ticket_limit) : 'نامحدود' }}</div></div>
                <div class="rounded-2xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">سقف هر کاربر</div><div class="mt-1 text-sm font-black text-slate-800">{{ $drawCampaign->ticket_limit_per_user ? number_format($drawCampaign->ticket_limit_per_user) : 'نامحدود' }}</div></div>
                <div class="rounded-2xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">جوایز</div><div class="mt-1 text-sm font-black text-slate-800">{{ number_format($drawCampaign->prizes->sum('quantity')) }} مورد</div></div>
                <div class="rounded-2xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">بلیط صادر شده</div><div class="mt-1 text-sm font-black text-slate-800">{{ number_format($drawCampaign->issued_tickets_count) }}</div></div>
            </div>
        </x-admin.metabox>
    @endif
</x-admin.page>
