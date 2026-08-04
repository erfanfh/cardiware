@extends('admin.layout')

@section('title', 'لیست دیدگاه‌ها')

@section('content')
    @include('admin.discussion.comments.partials.filters-panel')

    @php
        $commentIds = $comments->pluck('id')->values()->all();

        $commentStatuses = $comments
            ->mapWithKeys(fn($comment) => [
                $comment->id => $comment->status?->value,
            ])
            ->all();

        $commentPinnedStates = $comments
            ->mapWithKeys(fn($comment) => [
                $comment->id => (bool) $comment->is_pinned,
            ])
            ->all();
    @endphp

    <div
        x-data="{
            ...adminBulkSelection(),
            ...adminCommentActions({
                bulkActionUrl: @js(route('admin.comments.bulk-action')),
                csrfToken: @js(csrf_token()),
                initialStatuses: @js($commentStatuses),
                initialPins: @js($commentPinnedStates),
            })
        }"
        class="space-y-4"
    >
        <div class="rounded-2xl border px-4 py-2 transition-all duration-200 border-slate-200 bg-white">
            @php
                $baseQueryParams = request()->except(['status', 'type', 'page']);

                $statusTabs = [
                    [
                        'label' => 'همه',
                        'count' => $statusCounts['all'],
                        'href' => route('admin.comments.index', $baseQueryParams),
                        'active' => !request('status') && !request('type'),
                    ],
                    [
                        'label' => 'در انتظار تایید',
                        'count' => $statusCounts['pending'],
                        'href' => route('admin.comments.index', array_merge($baseQueryParams, ['status' => 'pending'])),
                        'active' => request('status') === 'pending',
                    ],
                    [
                        'label' => 'تایید شده',
                        'count' => $statusCounts['approved'],
                        'href' => route('admin.comments.index', array_merge($baseQueryParams, ['status' => 'approved'])),
                        'active' => request('status') === 'approved',
                    ],
                    [
                        'label' => 'رد شده',
                        'count' => $statusCounts['rejected'],
                        'href' => route('admin.comments.index', array_merge($baseQueryParams, ['status' => 'rejected'])),
                        'active' => request('status') === 'rejected',
                    ],
                ];
            @endphp

            <div class="flex flex-wrap items-center gap-2">
                @foreach($statusTabs as $tab)
                    <a
                        href="{{ $tab['href'] }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium transition-all duration-200
                    {{ $tab['active']
                        ? 'border-slate-900 bg-slate-900 text-white shadow-sm'
                        : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:border-slate-300'
                    }}"
                    >
                        <span>{{ $tab['label'] }}</span>

                        <span
                            class="inline-flex min-w-6 items-center justify-center rounded-lg px-1.5 py-0.5 text-xs font-bold
                    {{ $tab['active']
                        ? 'bg-white/15 text-white'
                        : 'bg-white text-slate-500 border border-slate-200'
                    }}"
                        >
                    {{ $tab['count'] }}
                </span>
                    </a>
                @endforeach
            </div>
        </div>

        <x-admin.list.bulk-bar
            :action="route('admin.comments.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند دیدگاه را انتخاب کنید."
            selected-text="دیدگاه انتخاب شده"
        />

        @include('admin.discussion.comments.partials.list-table')

        <x-admin.list.pagination :model="$comments"/>

        <div
            x-show="replyModal.open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        >
            <div
                class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
                x-on:click="closeReplyModal()"
                x-transition.opacity
            ></div>

            <div
                x-show="replyModal.open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-3 scale-95"
                class="relative z-10 w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
            >
                <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">
                                پاسخ به دیدگاه
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                در حال پاسخ به دیدگاه
                                <span class="font-bold text-slate-700" x-text="replyModal.author"></span>
                            </p>
                        </div>

                        <button
                            type="button"
                            class="cursor-pointer inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                            x-on:click="closeReplyModal()"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="mb-2 text-xs font-bold text-slate-500">
                            متن دیدگاه
                        </div>

                        <div
                            class="line-clamp-4 text-sm leading-7 text-slate-700"
                            x-text="replyModal.parentContent"
                        ></div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            متن پاسخ
                        </label>

                        <textarea
                            x-model="replyModal.content"
                            rows="6"
                            class="block w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-7 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100/60"
                            placeholder="پاسخ ادمین را اینجا بنویسید..."
                        ></textarea>

                        <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                            <span>
                                <span x-text="replyModal.content.length"></span>
                                /5000
                            </span>
                        </div>
                    </div>

                    <template x-if="replyModal.error">
                        <div
                            class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                            x-text="replyModal.error"
                        ></div>
                    </template>
                </div>

                <div
                    class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/80 px-5 py-4"
                >
                    <x-admin.form.button
                        type="button"
                        variant="secondary"
                        size="md"
                        x-on:click="closeReplyModal()"
                        x-bind:disabled="replyModal.processing"
                    >
                        انصراف
                    </x-admin.form.button>

                    <x-admin.form.button
                        type="button"
                        variant="solid"
                        size="md"
                        x-on:click="submitReply()"
                        x-bind:disabled="replyModal.processing"
                    >
                        <span x-show="!replyModal.processing">
                            ثبت پاسخ
                        </span>

                        <span x-show="replyModal.processing">
                            در حال ثبت...
                        </span>
                    </x-admin.form.button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/list-table.js', 'resources/js/admin/comment-actions.js'])
@endpush
