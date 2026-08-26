<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th
                width="70px"
                label="شناسه"
                sortable
                sortKey="id"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
            <x-admin.list.th>نظرسنجی</x-admin.list.th>
            <x-admin.list.th width="110px">وضعیت</x-admin.list.th>
            <x-admin.list.th width="130px">مخاطب</x-admin.list.th>
            <x-admin.list.th width="145px">نمایش نتیجه</x-admin.list.th>
            <x-admin.list.th
                width="105px"
                label="پاسخ‌ها"
                sortable
                sortKey="responses_count"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
            <x-admin.list.th
                width="105px"
                label="بازدیدها"
                sortable
                sortKey="views_count"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
            <x-admin.list.th
                width="175px"
                label="زمان‌بندی"
                sortable
                sortKey="starts_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($polls as $poll)
            @php
                $statusColor = match($poll->status) {
                    \App\Domain\Poll\Enums\PollStatusEnum::PUBLISHED => 'green',
                    \App\Domain\Poll\Enums\PollStatusEnum::ARCHIVED => 'yellow',
                    default => 'slate',
                };
            @endphp

            <x-admin.list.tr>
                <x-admin.list.td muted>
                    <span class="text-xs font-bold">#{{ $poll->id }}</span>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="min-w-[300px]">
                        <a href="{{ route('admin.polls.edit', $poll) }}"
                           class="inline-block max-w-xl truncate text-sm font-bold leading-6 text-slate-800 transition hover:text-brand-600">
                            {{ $poll->title }}
                        </a>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span dir="ltr">/{{ $poll->slug }}</span>
                            <span class="pb-1 text-slate-300">•</span>
                            <span>{{ number_format($poll->active_options_count) }} گزینه فعال</span>
                        </div>

                        <x-admin.list.row-actions>
                            <x-admin.list.action tag="a" href="{{ route('admin.polls.edit', $poll) }}" variant="primary">ویرایش</x-admin.list.action>
                            <span class="text-slate-300">|</span>
                            <x-admin.list.action tag="a" href="{{ route('admin.polls.analytics', $poll) }}">آمار و نتایج</x-admin.list.action>
                            @if($poll->status === \App\Domain\Poll\Enums\PollStatusEnum::PUBLISHED)
                                <span class="text-slate-300">|</span>
                                <x-admin.list.action tag="a" href="{{ route('polls.show', $poll->slug) }}" target="_blank">مشاهده</x-admin.list.action>
                            @endif
                            <span class="text-slate-300">|</span>
                            <x-admin.form
                                action="{{ route('admin.polls.destroy', $poll) }}"
                                method="delete"
                                class="js-confirm"
                                data-confirm-title="حذف نظرسنجی"
                                data-confirm-text="آیا از حذف نظرسنجی «{{ $poll->title }}» مطمئن هستید؟"
                            >
                                <x-admin.list.action tag="button" type="submit" variant="danger">حذف</x-admin.list.action>
                            </x-admin.form>
                        </x-admin.list.row-actions>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <x-admin.badge :color="$statusColor">{{ $poll->status->label() }}</x-admin.badge>
                </x-admin.list.td>

                <x-admin.list.td>
                    <x-admin.badge color="slate" size="md">{{ $poll->audience->label() }}</x-admin.badge>
                </x-admin.list.td>

                <x-admin.list.td>
                    <span class="text-xs font-bold text-slate-600">{{ $poll->results_visibility->label() }}</span>
                </x-admin.list.td>

                <x-admin.list.td>
                    <x-admin.badge size="md">{{ number_format($poll->responses_count) }}</x-admin.badge>
                </x-admin.list.td>

                <x-admin.list.td>
                    <x-admin.badge color="slate" size="md">{{ number_format($poll->views_count) }}</x-admin.badge>
                </x-admin.list.td>

                <x-admin.list.td muted>
                    <div class="text-[11px] leading-6">
                        <div>{{ $poll->starts_at ? 'شروع: '.jalali_date($poll->starts_at, 'Y/m/d H:i') : 'شروع بلافاصله' }}</div>
                        <div>{{ $poll->ends_at ? 'پایان: '.jalali_date($poll->ends_at, 'Y/m/d H:i') : 'بدون تاریخ پایان' }}</div>
                    </div>
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty
                title="نظرسنجی‌ای برای نمایش وجود ندارد"
                description="هنوز نظرسنجی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                action-text="افزودن نظرسنجی جدید"
                :action-href="route('admin.polls.create')"
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
