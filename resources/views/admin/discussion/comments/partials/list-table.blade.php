<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox
                    :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($commentIds) . ')'"
                    :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($commentIds) . ')'"
                />
            </x-admin.list.th>

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
                        'approved' => '!border-b-slate-200',
                        'pending' => '!bg-amber-50 !border-b-amber-200/70',
                        'rejected' => '!bg-rose-50 !border-b-rose-200/70',
                        default => '',
                    };

                    $replyBadgeClass = match ($comment->status?->value) {
                        'pending' => 'bg-amber-100 text-amber-700 border border-amber-200',
                        'rejected' => 'bg-rose-100 text-rose-700 border border-rose-200',
                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
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

                <x-admin.list.tr
                    data-comment-row="{{ $comment->id }}"
                    x-show="!isHidden({{ $comment->id }})"
                    x-transition.opacity.duration.200ms
                    :class="$rowClass"
                    ::class="{
                        'bg-slate-100/90 ring-1 ring-inset ring-slate-200': isSelected({{ $comment->id }}),
                        'opacity-60 pointer-events-none': isProcessing({{ $comment->id }})
                    }"
                >
                    <x-admin.list.td align="center">
                        <x-admin.list.bulk-checkbox
                            name="ids[]"
                            :value="$comment->id"
                            x-bind:checked="isSelected({{ $comment->id }})"
                            x-on:change="toggleSelection({{ $comment->id }})"
                        />
                    </x-admin.list.td>

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
                            @if($comment->isReply())
                                @php
                                    $parent = $comment->parent;
                                    $parentAuthor = $parent?->display_name ?? 'کاربر';
                                    $parentContent = $parent?->content ?? 'دیدگاه والد در دسترس نیست.';
                                @endphp

                                <div class="mb-2 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                                    <span
                                        class="inline-flex items-center rounded-lg border px-2 py-1 text-[11px] font-medium"
                                        :class="replyBadgeClass({{ $comment->id }})"
                                    >
                                        پاسخ
                                    </span>

                                    <span>
                                        این یک پاسخ به دیدگاه
                                        <span class="font-bold text-slate-700">{{ $parentAuthor }}</span>
                                        است
                                    </span>

                                    <span
                                        x-data="{
                                            open: false,
                                            position: 'right',
                                            timer: null,

                                            show() {
                                                clearTimeout(this.timer);

                                                this.timer = setTimeout(() => {
                                                    this.open = true;

                                                    this.$nextTick(() => {
                                                        const rect = this.$refs.tooltip.getBoundingClientRect();

                                                        if (rect.left < 12) {
                                                            this.position = 'left';
                                                        }

                                                        if (rect.right > window.innerWidth - 12) {
                                                            this.position = 'right';
                                                        }
                                                    });
                                                }, 180);
                                            },

                                            hide() {
                                                clearTimeout(this.timer);
                                                this.open = false;
                                            }
                                        }"
                                        x-on:mouseenter="show()"
                                        x-on:mouseleave="hide()"
                                        x-on:focusin="show()"
                                        x-on:focusout="hide()"
                                        class="relative inline-flex items-center"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-slate-200 bg-white text-[11px] font-bold text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700"
                                        >
                                            ?
                                        </button>

                                        <span
                                            x-ref="tooltip"
                                            x-show="open"
                                            x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                            x-cloak
                                            class="pointer-events-none absolute top-7 z-30 w-80 rounded-2xl border border-slate-200 bg-white p-3 text-right text-xs leading-6 text-slate-700 shadow-xl"
                                            :class="{
                                                'right-0 origin-top-right': position === 'right',
                                                'left-0 origin-top-left': position === 'left'
                                            }"
                                        >
                                            <span class="mb-1 block font-bold text-slate-800">
                                                متن دیدگاه والد:
                                            </span>

                                            <span>
                                                {!! nl2br(htmlspecialchars(e($parentContent))) !!}
                                            </span>
                                        </span>
                                    </span>
                                </div>
                            @endif

                            <div class="text-sm leading-7 text-slate-700">
                                {!! nl2br(htmlspecialchars(e($comment->content))) !!}
                            </div>

                            <x-admin.list.row-actions x-cloak class="mt-2">
                                <template x-if="rowStatus({{ $comment->id }}) !== 'approved'">
                                    <x-admin.list.action
                                        tag="button"
                                        type="button"
                                        x-on:click="runAction({{ $comment->id }}, 'approve')"
                                        x-bind:disabled="isProcessing({{ $comment->id }})"
                                    >
                                        تایید
                                    </x-admin.list.action>
                                </template>

                                <template
                                    x-if="rowStatus({{ $comment->id }}) !== 'approved' && rowStatus({{ $comment->id }}) !== 'rejected'">
                                    <span class="text-slate-300">|</span>
                                </template>

                                <template x-if="rowStatus({{ $comment->id }}) !== 'rejected'">
                                    <x-admin.list.action
                                        tag="button"
                                        type="button"
                                        x-on:click="runAction({{ $comment->id }}, 'reject')"
                                        x-bind:disabled="isProcessing({{ $comment->id }})"
                                    >
                                        رد
                                    </x-admin.list.action>
                                </template>

                                <span class="text-slate-300">|</span>

                                <x-admin.list.action
                                    tag="button"
                                    type="button"
                                    variant="danger"
                                    x-on:click="confirmAndRun({{ $comment->id }}, 'delete', 'آیا از حذف این دیدگاه مطمئن هستید؟')"
                                    x-bind:disabled="isProcessing({{ $comment->id }})"
                                >
                                    حذف
                                </x-admin.list.action>

                                <template x-if="isProcessing({{ $comment->id }})">
                                    <span class="text-slate-400">در حال انجام...</span>
                                </template>
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
                description="هنوز هیچ دیدگاهی ثبت نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
            />
        @endif
    </x-admin.list.tbody>
</x-admin.list.table>
