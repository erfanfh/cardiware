<div
    x-show="editModal.open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    x-on:keydown.escape.window="closeEditModal()"
>
    <div
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
        x-on:click="closeEditModal()"
        x-transition.opacity
    ></div>

    <div
        x-show="editModal.open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-3 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="translate-y-3 scale-95 opacity-0"
        class="relative z-10 w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="edit-comment-title"
    >
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 id="edit-comment-title" class="text-base font-bold text-slate-800">
                        ویرایش دیدگاه
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        دیدگاه ثبت‌شده توسط
                        <span class="font-bold text-slate-700" x-text="editModal.author"></span>
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    x-on:click="closeEditModal()"
                    aria-label="بستن پنجره ویرایش"
                >
                    ×
                </button>
            </div>
        </div>

        <div class="space-y-5 p-5">
            <div>
                <label for="edit-comment-content" class="mb-2 block text-sm font-bold text-slate-700">
                    متن دیدگاه
                </label>

                <textarea
                    id="edit-comment-content"
                    x-ref="editCommentContent"
                    x-model="editModal.content"
                    rows="7"
                    maxlength="5000"
                    class="block w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-7 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100/60"
                    placeholder="متن دیدگاه را وارد کنید..."
                ></textarea>

                <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                    <span>حداقل ۲ کاراکتر</span>
                    <span><span x-text="editModal.content.length"></span> / ۵۰۰۰</span>
                </div>
            </div>

            <template x-if="editModal.canEditRating">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="mb-3">
                        <div class="text-sm font-bold text-slate-700">امتیاز کاربر</div>
                        <p class="mt-1 text-xs leading-6 text-slate-500">
                            امتیاز جدید را از ۱ تا ۵ ستاره انتخاب کنید.
                        </p>
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-3"
                        x-on:mouseleave="editModal.hoverRating = 0"
                    >
                        <div class="flex items-center gap-0.5">
                            @for($n = 1; $n <= 5; $n++)
                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl p-1 transition duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-amber-100"
                                    x-on:mouseenter="editModal.hoverRating = {{ $n }}"
                                    x-on:click="editModal.rating = {{ $n }}"
                                    aria-label="ثبت امتیاز {{ $n }} از ۵"
                                >
                                    <svg
                                        viewBox="0 0 20 20"
                                        class="h-7 w-7 transition-colors"
                                        :class="(editModal.hoverRating || editModal.rating) >= {{ $n }} ? 'text-amber-400' : 'text-slate-200'"
                                        aria-hidden="true"
                                    >
                                        <path
                                            fill="currentColor"
                                            d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                                        />
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        <span
                            class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600"
                            x-text="editModal.rating ? `${editModal.rating} از ۵` : 'انتخاب نشده'"
                        ></span>
                    </div>
                </div>
            </template>

            <template x-if="editModal.error">
                <div
                    class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                    x-text="editModal.error"
                ></div>
            </template>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/80 px-5 py-4">
            <x-admin.form.button
                type="button"
                variant="secondary"
                size="md"
                x-on:click="closeEditModal()"
                x-bind:disabled="editModal.processing"
            >
                انصراف
            </x-admin.form.button>

            <x-admin.form.button
                type="button"
                variant="solid"
                size="md"
                x-on:click="submitEdit()"
                x-bind:disabled="editModal.processing"
            >
                <span x-show="!editModal.processing">ذخیره تغییرات</span>
                <span x-show="editModal.processing">در حال ذخیره...</span>
            </x-admin.form.button>
        </div>
    </div>
</div>
