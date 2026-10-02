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
                    $commentableUrl = null;

                    if ($commentable) {
                        if (isset($commentable->title) && filled($commentable->title)) {
                            $commentableTitle = $commentable->title;
                        } elseif (isset($commentable->name) && filled($commentable->name)) {
                            $commentableTitle = $commentable->name;
                        }

                        $commentableUrl = $commentable->url ?? null;
                    }

                    $resolvedCommentableClass = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($comment->commentable_type)
                        ?? $comment->commentable_type;

                    $commentableTypeLabel = match ($resolvedCommentableClass) {
                        \App\Domain\Content\Models\Post::class => 'نوشته',
                        \App\Domain\Catalog\Models\Product::class => 'محصول',
                        \App\Domain\Video\Models\Video::class => 'ویدیو',
                        default => 'محتوا',
                    };

                    $commentableTypeBadgeClass = match ($resolvedCommentableClass) {
                        \App\Domain\Content\Models\Post::class => 'border-sky-200 bg-sky-50 text-sky-700',
                        \App\Domain\Catalog\Models\Product::class => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        \App\Domain\Video\Models\Video::class => 'border-orange-200 bg-orange-50 text-orange-700',
                        default => 'border-slate-200 bg-slate-50 text-slate-600',
                    };
                @endphp

                <x-admin.list.tr
                    id="comment-{{ $comment->id }}"
                    data-comment-row="{{ $comment->id }}"
                    class="scroll-mt-24"
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
                                    $parentRenderedContent = $parent?->safe_content
                                        ?? e('دیدگاه والد در دسترس نیست.');
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

                                    <div
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

                                        <div
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

                                            <div class="tinymce-content comment-content">
                                                {!! $parentRenderedContent !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div
                                x-show="isPinned({{ $comment->id }})"
                                x-cloak
                                class="mb-2"
                            >
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 [&_svg]:h-3.5 [&_svg]:w-3.5"
                                >
                                    @include('icons.pin')

                                    دیدگاه پین شده
                                </span>
                            </div>

                            @if($comment->isRoot())
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <div class="flex items-center gap-0.5" aria-label="امتیاز دیدگاه">
                                        @for($n = 1; $n <= 5; $n++)
                                            <svg
                                                viewBox="0 0 20 20"
                                                class="h-4 w-4 transition-colors"
                                                :class="commentRating({{ $comment->id }}) >= {{ $n }} ? 'text-amber-400' : 'text-slate-200'"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                                                />
                                            </svg>
                                        @endfor
                                    </div>

                                    <span
                                        class="text-[11px] font-bold text-slate-400"
                                        x-text="commentRating({{ $comment->id }}) ? `${commentRating({{ $comment->id }})} از ۵` : 'بدون امتیاز'"
                                    ></span>
                                </div>
                            @endif

                            <div
                                class="tinymce-content comment-content text-sm leading-7 text-slate-700"
                                x-html="commentRenderedContent({{ $comment->id }})"
                            >{!! $comment->safe_content !!}</div>

                            <x-admin.list.row-actions x-cloak class="mt-4">
                                <x-admin.list.action
                                    tag="button"
                                    type="button"
                                    x-on:click="openEditModal({{ $comment->id }})"
                                    x-bind:disabled="isProcessing({{ $comment->id }})"
                                >
                                    ویرایش
                                </x-admin.list.action>

                                <span class="text-slate-300">|</span>
                                {{-- Pending: approve / reject / delete / reply --}}
                                <template x-if="rowStatus({{ $comment->id }}) === 'pending'">
                                    <div class="inline-flex items-center gap-2">
                                        <x-admin.list.action
                                            tag="button"
                                            type="button"
                                            x-on:click="runAction({{ $comment->id }}, 'approve')"
                                            x-bind:disabled="isProcessing({{ $comment->id }})"
                                        >
                                            تأیید
                                        </x-admin.list.action>

                                        <span class="text-slate-300">|</span>

                                        <x-admin.list.action
                                            tag="button"
                                            type="button"
                                            x-on:click="runAction({{ $comment->id }}, 'reject')"
                                            x-bind:disabled="isProcessing({{ $comment->id }})"
                                        >
                                            رد کردن
                                        </x-admin.list.action>
                                    </div>
                                </template>

                                {{-- Approved: pending / reject / delete / reply --}}
                                <template x-if="rowStatus({{ $comment->id }}) === 'approved'">
                                    <div class="inline-flex items-center gap-2">
                                        <x-admin.list.action
                                            tag="button"
                                            type="button"
                                            x-on:click="runAction({{ $comment->id }}, 'pending')"
                                            x-bind:disabled="isProcessing({{ $comment->id }})"
                                        >
                                            انتقال به انتظار
                                        </x-admin.list.action>

                                        <span class="text-slate-300">|</span>

                                        <x-admin.list.action
                                            tag="button"
                                            type="button"
                                            x-on:click="runAction({{ $comment->id }}, 'reject')"
                                            x-bind:disabled="isProcessing({{ $comment->id }})"
                                        >
                                            رد کردن
                                        </x-admin.list.action>
                                    </div>
                                </template>

                                {{-- Rejected: pending / approve / delete / reply --}}
                                <template x-if="rowStatus({{ $comment->id }}) === 'rejected'">
                                    <div class="inline-flex items-center gap-2">
                                        <x-admin.list.action
                                            tag="button"
                                            type="button"
                                            x-on:click="runAction({{ $comment->id }}, 'pending')"
                                            x-bind:disabled="isProcessing({{ $comment->id }})"
                                        >
                                            انتقال به انتظار
                                        </x-admin.list.action>

                                        <span class="text-slate-300">|</span>

                                        <x-admin.list.action
                                            tag="button"
                                            type="button"
                                            x-on:click="runAction({{ $comment->id }}, 'approve')"
                                            x-bind:disabled="isProcessing({{ $comment->id }})"
                                        >
                                            تأیید
                                        </x-admin.list.action>
                                    </div>
                                </template>

                                @if($comment->isRoot())
                                    <template
                                        x-if="rowStatus({{ $comment->id }}) === 'approved'"
                                    >
                                        <div class="inline-flex items-center gap-2">
                                            <span class="text-slate-300">|</span>

                                            <button
                                                type="button"
                                                x-on:click="togglePin(
                                                    {{ $comment->id }},
                                                    @js(route('admin.comments.toggle-pin', $comment))
                                                )"
                                                x-bind:disabled="isProcessing({{ $comment->id }})"
                                                x-bind:class="{
                                                    'text-amber-600 hover:text-amber-700': isPinned({{ $comment->id }}),
                                                    'text-slate-600 hover:text-brand-600': !isPinned({{ $comment->id }})
                                                }"
                                                class="cursor-pointer text-xs font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                <span
                                                    x-text="
                                                        isPinned({{ $comment->id }})
                                                            ? 'برداشتن پین'
                                                            : 'پین کردن'
                                                    "
                                                ></span>
                                            </button>
                                        </div>
                                    </template>
                                @endif

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

                                <span class="text-slate-300">|</span>

                                <button
                                    type="button"
                                    class="cursor-pointer text-xs font-medium text-slate-600 transition hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-50"
                                    x-on:click="openReplyModal({
                                        id: {{ $comment->id }},
                                        author: @js($comment->display_name),
                                        content: commentContent({{ $comment->id }}),
                                        replyUrl: @js(route('admin.comments.reply', $comment)),
                                    })"
                                    x-bind:disabled="isProcessing({{ $comment->id }})"
                                >
                                    پاسخ
                                </button>

                                <template x-if="isProcessing({{ $comment->id }})">
                                    <span class="text-slate-400">در حال انجام...</span>
                                </template>
                            </x-admin.list.row-actions>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[240px] space-y-2">
                            <div>
                                <span
                                    class="inline-flex items-center rounded-xl border px-2.5 py-1 text-[11px] font-bold {{ $commentableTypeBadgeClass }}">
                                    {{ $commentableTypeLabel }}
                                </span>
                            </div>

                            @if($commentableUrl)
                                <a
                                    href="{{ $commentableUrl }}"
                                    target="_blank"
                                    class="line-clamp-2 text-sm font-bold leading-7 text-slate-700 transition hover:text-brand-600"
                                >
                                    {{ $commentableTitle }}
                                </a>
                            @else
                                <div class="line-clamp-2 text-sm font-bold leading-7 text-slate-700">
                                    {{ $commentableTitle }}
                                </div>
                            @endif

                            <div class="text-xs text-slate-400">
                                {{ class_basename($resolvedCommentableClass) }}
                                #{{ $comment->commentable_id }}
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
