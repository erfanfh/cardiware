<x-admin.metabox title="تاریخچه عملیات" :collapsible="true">
    <div class="space-y-2">
        @forelse($recentAuditEvents as $auditEvent)
            <div class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-xs font-black text-slate-800">{{ $auditEvent->label() }}</div>
                    <div class="mt-1 text-[10px] text-slate-400">
                        {{ $auditEvent->actor?->full_name ?: 'سیستم' }}
                        @if($auditEvent->subject_type && $auditEvent->subject_id)
                            · {{ $auditEvent->subject_type }} #{{ $auditEvent->subject_id }}
                        @endif
                    </div>
                </div>
                <time class="text-[10px] font-bold text-slate-400">{{ jalali_date($auditEvent->occurred_at, 'Y/m/d H:i:s') }}</time>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-5 text-center text-xs text-slate-400">رویداد جدیدی ثبت نشده است. از این پس تمام عملیات حساس در این بخش نگهداری می‌شوند.</div>
        @endforelse
    </div>
</x-admin.metabox>
