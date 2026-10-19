<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th>عنوان</x-admin.list.th>
            <x-admin.list.th width="130px">نوع</x-admin.list.th>
            <x-admin.list.th width="140px">وضعیت نمایش</x-admin.list.th>
            <x-admin.list.th>بازه نمایش</x-admin.list.th>
            <x-admin.list.th width="130px">زمان ایجاد</x-admin.list.th>
        </tr>
    </x-admin.list.thead>
    <x-admin.list.tbody>
        @forelse($announcements as $announcement)
            @php
                $statusLabel = $announcement->statusLabel();
                $statusColor = match ($statusLabel) {
                    'در حال نمایش' => 'green',
                    'زمان‌بندی‌شده' => 'yellow',
                    'غیرفعال' => 'red',
                    default => 'slate',
                };
                $typeColor = match ($announcement->type) {
                    'warning' => 'yellow',
                    'success' => 'green',
                    default => 'blue',
                };
            @endphp

            <x-admin.list.tr>
                <x-admin.list.td>
                    <div class="min-w-[280px]">
                        <a
                            href="{{ route('admin.account-announcements.edit', $announcement) }}"
                            class="inline-block leading-6 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                        >
                            {{ $announcement->title }}
                        </a>

                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span>#{{ $announcement->id }}</span>
                        </div>

                        <x-admin.list.row-actions>
                            <x-admin.list.action
                                tag="a"
                                href="{{ route('admin.account-announcements.edit', $announcement) }}"
                                variant="primary"
                            >
                                ویرایش
                            </x-admin.list.action>

                            <span class="text-slate-300">|</span>

                            <x-admin.form
                                action="{{ route('admin.account-announcements.destroy', $announcement) }}"
                                method="delete"
                                class="js-confirm"
                                data-confirm-title="حذف اطلاعیه"
                                data-confirm-text="آیا از حذف اطلاعیه «{{ $announcement->title }}» از حساب همه کاربران مطمئن هستید؟ این عملیات قابل بازگشت نیست."
                            >
                                <x-admin.list.action tag="button" type="submit" variant="danger">
                                    حذف
                                </x-admin.list.action>
                            </x-admin.form>
                        </x-admin.list.row-actions>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <x-admin.badge :color="$typeColor" size="md" class="whitespace-nowrap">
                        {{ \App\Domain\Notification\Models\AccountAnnouncement::TYPES[$announcement->type] ?? 'اطلاع‌رسانی' }}
                    </x-admin.badge>
                </x-admin.list.td>

                <x-admin.list.td>
                    <x-admin.badge :color="$statusColor" size="md" class="whitespace-nowrap">
                        {{ $statusLabel }}
                    </x-admin.badge>
                </x-admin.list.td>

                <x-admin.list.td muted>
                    <div class="space-y-1 text-xs leading-6">
                        <div class="whitespace-nowrap">از: {{ $announcement->starts_at ? jalali_date($announcement->starts_at, 'Y/m/d H:i') : 'بلافاصله پس از فعال‌سازی' }}</div>
                        <div class="whitespace-nowrap">تا: {{ $announcement->ends_at ? jalali_date($announcement->ends_at, 'Y/m/d H:i') : 'بدون محدودیت' }}</div>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td muted>
                    <div class="text-xs leading-6">
                        <div>{{ jalali_date($announcement->created_at, 'Y/m/d') }}</div>
                        <div>{{ jalali_date($announcement->created_at, 'H:i') }}</div>
                    </div>
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty colspan="5" title="اطلاعیه‌ای پیدا نشد" description="یک اطلاعیه جدید بسازید یا فیلترها را تغییر دهید." action-text="اطلاعیه جدید" :action-href="route('admin.account-announcements.create')"/>
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
