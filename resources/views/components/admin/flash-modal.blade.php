@php
    $successMessage = session('success');
    $errorMessage   = session('error');

    $type = $successMessage ? 'success' : ($errorMessage ? 'error' : null);
    $message = $successMessage ?? $errorMessage;

    if ($type === 'success') {
        $title       = session('success_title', 'عملیات موفق');
        $icon        = '✓';
        $accentClass = 'border-[var(--bg-chip)]';
        $actionUrl   = session('success_action_url');
        $actionLabel = session('success_action_label', 'مشاهده');
    } elseif ($type === 'error') {
        $title       = session('error_title', 'خطا');
        $icon        = '!';
        $accentClass = 'border-[var(--brand-100)]';
        $actionUrl   = session('error_action_url');
        $actionLabel = session('error_action_label', 'جزئیات بیشتر');
    } else {
        $title = $icon = $accentClass = $actionUrl = $actionLabel = null;
    }
@endphp

@if($type && $message)
    <div
        class="fixed inset-0 bg-[rgba(15,23,42,0.45)] dark:bg-[rgba(0,0,0,0.6)] backdrop-blur-[10px] flex items-center justify-center z-[9998] transition-opacity duration-200 ease-out opacity-100 pointer-events-auto"
        data-modal-backdrop
        id="flash-modal-backdrop">
        <div
            class="relative w-full max-w-[380px] mx-4 rounded-[24px] px-[20px] py-[22px] pb-[16px] bg-white dark:bg-gray-800 shadow-[0_24px_60px_rgba(15,23,42,0.35)] dark:shadow-[0_24px_60px_rgba(0,0,0,0.5)] border border-[var(--border-subtle)] dark:border-gray-700 animate-[za-modal-pop-in_0.2s_ease-out] sm:rounded-[18px] sm:px-4{{ $accentClass }}">
            <button type="button"
                    class="absolute top-[10px] end-[12px] border-0 bg-transparent text-[1.4rem] leading-none cursor-pointer text-[var(--darker-text-muted)] dark:text-gray-400 transition-colors duration-150 ease hover:text-[var(--text-dark-gray)] dark:hover:text-gray-200"
                    data-modal-close aria-label="بستن">
                &times;
            </button>

            <div class="flex justify-center mb-[1.25rem]">
                <div
                    class="w-[46px] h-[46px] rounded-full flex items-center justify-center bg-[linear-gradient(135deg,var(--brand-500),var(--brand-600))] dark:bg-[linear-gradient(135deg,var(--brand-400),var(--brand-500))] text-[var(--text-invert)] text-[1.4rem] font-bold shadow-[0_10px_25px_rgba(244,63,94,0.6)] dark:shadow-[0_10px_25px_rgba(244,63,94,0.3)]">
                    {{ $icon }}
                </div>
            </div>

            <div class="text-center py-0 px-[6px]">
                <h2 class="m-0 mb-[4px] text-[1.1rem] font-bold text-[var(--text-main)] dark:text-gray-100">{{ $title }}</h2>
                <p class="m-0 text-[0.88rem] text-[var(--text-muted)] dark:text-gray-400">{{ $message }}</p>
            </div>

            <div class="flex justify-center gap-2 mt-4">
                @if($actionUrl)
                    <a href="{{ $actionUrl }}"
                       class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-50 ease-out text-[var(--text-main)] dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ $actionLabel }}
                    </a>
                @endif

                <button type="button"
                        class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-50 ease-out text-[var(--text-main)] dark:text-gray-100 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600"
                        data-modal-close>
                    باشه
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const backdrop = document.querySelector('#flash-modal-backdrop');
            if (!backdrop) return;

            function closeModal() {
                backdrop.style.opacity = '0';
                backdrop.style.pointerEvents = 'none';
                setTimeout(() => {
                    backdrop.remove();  // حتماً حذف کامل از DOM
                }, 250);
            }

            // کلیک روی backdrop
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop || e.target.hasAttribute('data-modal-close')) {
                    closeModal();
                }
            });

            // ESC
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });

            // کلیک روی دکمه "باشه" و ضربدر
            const closeButtons = backdrop.querySelectorAll('[data-modal-close]');
            closeButtons.forEach(btn => {
                btn.addEventListener('click', closeModal);
            });
        });
    </script>
@endif

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- SweetAlert2 Modals --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ========== Select All Checkbox (کاربرد برای همه صفحات) ==========
        const selectAllCheckbox = document.getElementById('select-all');
        const itemCheckboxes = document.querySelectorAll('input[name="ids[]"]');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);

                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                }
            });
        });
        // ========== Helper: Get item type text ==========
        function getItemTypeText(itemType) {
            const texts = {
                category: {
                    singular: 'دسته‌بندی',
                    plural: 'دسته‌بندی‌ها'
                },
                tag: {
                    singular: 'برچسب',
                    plural: 'برچسب‌ها'
                }
            };
            return texts[itemType] || texts.category;
        }

        // ========== Slug Change Confirmation ==========
        window.confirmSlugChange = function (originalSlug, currentSlug, form, confirmInput, itemType = 'category') {
            const texts = getItemTypeText(itemType);

            Swal.fire({
                title: '⚠️ تأیید تغییر اسلاگ',
                html: `
                    <div class="text-right">
                        <p class="mb-3">آیا مطمئن هستید که می‌خواهید اسلاگ را تغییر دهید؟</p>
                        <p class="font-medium mb-2">اسلاگ قبلی: <span class="text-gray-600">${originalSlug}</span></p>
                        <p class="font-medium mb-3">اسلاگ جدید: <span class="text-blue-600">${currentSlug}</span></p>
                        <p class="text-sm text-red-600 dark:text-red-400">
                            ⚠️ تغییر اسلاگ باعث تغییر آدرس این ${texts.singular} می‌شود.<br>
                            ممکن است لینک‌های قدیمی شکسته شوند و روی سئو تأثیر بگذارد.
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله، تغییر بده',
                cancelButtonText: 'لغو',
                reverseButtons: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                customClass: {
                    popup: 'rtl-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    confirmInput.value = '1';
                    form.submit();
                }
            });
        };

        // ========== Bulk Delete Confirmation ==========
        window.confirmBulkDelete = function (checkedCount, checkedCheckboxes, bulkForm, itemType = 'category') {
            const texts = getItemTypeText(itemType);

            Swal.fire({
                title: '⚠️ تأیید حذف گروهی',
                html: `
                    <div class="text-right">
                        <p class="mb-3">آیا مطمئن هستید که می‌خواهید <strong>${checkedCount}</strong> ${texts.singular} انتخاب‌شده را حذف کنید؟</p>
                        <p class="text-sm text-red-600 dark:text-red-400">
                            ⚠️ این عملیات غیرقابل بازگشت است!<br>
                            تمام ${texts.plural} انتخاب‌شده حذف خواهند شد.
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'لغو',
                reverseButtons: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                customClass: {
                    popup: 'rtl-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    checkedCheckboxes.forEach(checkbox => {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'ids[]';
                        hiddenInput.value = checkbox.value;
                        bulkForm.appendChild(hiddenInput);
                    });
                    bulkForm.submit();
                }
            });
        };

        // ========== No Items Selected Warning ==========
        window.showNoItemsSelected = function (itemType = 'category') {
            const texts = getItemTypeText(itemType);

            Swal.fire({
                title: '❌ هشدار',
                text: `لطفاً حداقل یک ${texts.singular} انتخاب کنید.`,
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                customClass: {
                    popup: 'rtl-popup'
                }
            });
        };
    });
</script>

<style>
    .rtl-popup {
        direction: rtl;
        text-align: right;
    }

    .rtl-popup .swal2-title {
        text-align: right;
    }

    .rtl-popup .swal2-html-container {
        text-align: right;
    }

    .rtl-popup .swal2-actions {
        text-align: center;
    }
</style>

<div id="confirm-backdrop"
     class="fixed inset-0 bg-[rgba(15,23,42,0.45)] dark:bg-[rgba(0,0,0,0.6)] backdrop-blur-[10px] flex items-center justify-center z-[9998] transition-opacity duration-200 ease-out opacity-0 pointer-events-none"
     data-confirm-backdrop>
    <div
        class="relative w-full max-w-[380px] mx-4 rounded-[24px] px-[20px] py-[22px] pb-[16px] bg-white dark:bg-gray-800 shadow-[0_24px_60px_rgba(15,23,42,0.35)] dark:shadow-[0_24px_60px_rgba(0,0,0,0.5)] border border-[var(--border-subtle)] dark:border-gray-700 animate-[za-modal-pop-in_0.2s_ease-out] sm:rounded-[18px] sm:px-4">
        <button type="button"
                class="absolute top-[10px] end-[12px] border-0 bg-transparent text-[1.4rem] leading-none cursor-pointer text-[var(--darker-text-muted)] dark:text-gray-400 transition-colors duration-150 ease hover:text-[var(--text-dark-gray)] dark:hover:text-gray-200"
                data-confirm-cancel>
            &times;
        </button>

        <div class="flex justify-center mb-[1.25rem]">
            <div
                class="w-[46px] h-[46px] rounded-full flex items-center justify-center bg-[linear-gradient(135deg,var(--brand-500),var(--brand-600))] dark:bg-[linear-gradient(135deg,var(--brand-400),var(--brand-500))] text-[var(--text-invert)] text-[1.4rem] font-bold shadow-[0_10px_25px_rgba(244,63,94,0.6)] dark:shadow-[0_10px_25px_rgba(244,63,94,0.3)]">
                !
            </div>
        </div>

        <div class="text-center py-0 px-[6px]">
            <h2 class="m-0 mb-[4px] text-[1.1rem] font-bold text-[var(--text-main)] dark:text-gray-100" data-confirm-title>
                تأیید عملیات
            </h2>
            <p class="m-0 text-[0.88rem] text-[var(--text-muted)] dark:text-gray-400" data-confirm-text>
                آیا مطمئن هستید؟
            </p>
        </div>

        <div class="flex justify-center gap-2 mt-4">
            <button type="button"
                    class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-50 ease-out text-[var(--text-main)] dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700"
                    data-confirm-cancel>
                انصراف
            </button>

            <button type="button"
                    class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-50 ease-out text-[var(--text-main)] dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700"
                    data-confirm-submit>
                بله، ادامه
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let activeForm = null;

        const backdrop = document.querySelector('[data-confirm-backdrop]');
        const titleEl = backdrop.querySelector('[data-confirm-title]');
        const textEl = backdrop.querySelector('[data-confirm-text]');
        const submitBtn = backdrop.querySelector('[data-confirm-submit]');
        const cancelBtns = backdrop.querySelectorAll('[data-confirm-cancel]');

        function openModal({title, text}) {
            if (!backdrop) return;
            titleEl.textContent = title;
            textEl.textContent = text;
            backdrop.classList.remove('opacity-0 pointer-events-none');
            backdrop.classList.add('opacity-100 pointer-events-auto');
        }

        function closeModal() {
            if (!backdrop) return;
            backdrop.classList.remove('opacity-100 pointer-events-auto');
            backdrop.classList.add('opacity-0 pointer-events-none');
            activeForm = null;
        }

        // گرفتن submit فرم‌ها
        document.addEventListener('submit', e => {
            const form = e.target;
            if (!form.classList.contains('js-confirm')) return;

            e.preventDefault();
            activeForm = form;

            openModal({
                title: form.dataset.confirmTitle || 'آیا اطمینان دارید؟',
                text: form.dataset.confirmText || 'این عملیات قابل بازگشت نیست.'
            });
        });

        submitBtn.addEventListener('click', () => {
            if (activeForm) activeForm.submit();
        });

        cancelBtns.forEach(btn =>
            btn.addEventListener('click', closeModal)
        );

        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closeModal();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModal();
        });
    });
</script>
