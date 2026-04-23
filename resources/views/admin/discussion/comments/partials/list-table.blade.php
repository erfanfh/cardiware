<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th>نویسنده</x-admin.list.th>
            <x-admin.list.th>متن دیدگاه</x-admin.list.th>
            <x-admin.list.th>محتوای مرتبط</x-admin.list.th>
            <x-admin.list.th
                    width="130px"
                    label="تاریخ ارسال"
                    sortable
                    sortKey="created_at"
                    :current-sort="request('sort')"
                    :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @if($comments->isNotEmpty())
            @foreach($comments as $comment)
                @php
                    $rowClass = match ($comment->status?->value) {
                        'approved' => '!bg-slate-50/80',
                        'pending' => '!bg-amber-50/70',
                        'rejected' => '!bg-rose-50/70',
                        default => '',
                    };

                    $commentable = $comment->commentable;

                    $commentableTitle = '---';

                    if ($commentable) {
                        if (isset($commentable->title) && filled($commentable->title)) {
                            $commentableTitle = $commentable->title;
                        } elseif (isset($commentable->name) && filled($commentable->name)) {
                            $commentableTitle = $commentable->name;
                        }
                    }

                    $commentableType = $commentable ? class_basename($commentable) : '---';
                @endphp

                <x-admin.list.tr :class="$rowClass">
                    <x-admin.list.td>
                        <div class="min-w-[220px] space-y-2">
                            <div class="text-sm font-bold text-slate-800">
                                {{ $comment->display_name }}
                            </div>

                            <div class="space-y-1 text-xs text-slate-500">
                                <div>#{{ $comment->id }}</div>

                                @if($comment->author_email)
                                    <div dir="ltr">{{ $comment->author_email }}</div>
                                @endif

                                @if($comment->ip_address)
                                    <div dir="ltr">{{ $comment->ip_address }}</div>
                                @endif
                            </div>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[340px]">
                            <div class="line-clamp-4 text-sm leading-7 text-slate-700">
                                {{ $comment->content }}
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                @if($comment->isRoot())
                                    <x-admin.badge size="md" class="whitespace-nowrap">
                                        دیدگاه اصلی
                                    </x-admin.badge>
                                @else
                                    <x-admin.badge color="slate" size="md" class="whitespace-nowrap">
                                        پاسخ به #{{ $comment->parent_id }}
                                    </x-admin.badge>
                                @endif
                            </div>

                            <x-admin.list.row-actions>
                                <x-admin.list.action tag="button" type="button">
                                    تایید
                                </x-admin.list.action>

                                <span class="text-slate-300">|</span>

                                <x-admin.list.action tag="button" type="button">
                                    رد
                                </x-admin.list.action>

                                <span class="text-slate-300">|</span>

                                <x-admin.list.action tag="button" type="button" variant="danger">
                                    حذف
                                </x-admin.list.action>
                            </x-admin.list.row-actions>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[220px] space-y-2">
                            <div class="text-sm font-bold text-slate-800">
                                {{ $commentableTitle }}
                            </div>

                            <div class="text-xs text-slate-500">
                                نوع: {{ $commentableType }}
                            </div>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td muted>
                        <div class="text-xs leading-6">
                            <div>{{ jalali_date($comment->created_at, 'Y/m/d') }}</div>
                            <div>{{ jalali_date($comment->created_at, 'H:i') }}</div>
                        </div>
                    </x-admin.list.td>
                </x-admin.list.tr>
            @endforeach
        @else
            <x-admin.list.empty
                    title="دیدگاهی برای نمایش وجود ندارد"
                    description="هنوز هیچ دیدگاهی ثبت نشده است."
            />
        @endif
    </x-admin.list.tbody>
</x-admin.list.table>
