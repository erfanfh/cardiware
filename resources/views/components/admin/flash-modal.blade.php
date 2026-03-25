@php
    $successMessage = session('success');
    $errorMessage = session('error');

    $type = $successMessage ? 'success' : ($errorMessage ? 'error' : null);
    $message = $successMessage ?? $errorMessage;

    if ($type === 'success') {
        $title = session('success_title', 'عملیات موفق');
        $icon = '✓';
        $accentClass = 'border-[var(--bg-chip)]';
        $actionUrl = session('success_action_url');
        $actionLabel = session('success_action_label', 'مشاهده');
    } elseif ($type === 'error') {
        $title = session('error_title', 'خطا');
        $icon = '!';
        $accentClass = 'border-[var(--brand-100)]';
        $actionUrl = session('error_action_url');
        $actionLabel = session('error_action_label', 'جزئیات بیشتر');
    } else {
        $title = null;
        $icon = null;
        $accentClass = null;
        $actionUrl = null;
        $actionLabel = null;
    }
@endphp

@if($type && $message)
    <div
        id="flash-modal-backdrop"
        data-flash-modal
        class="fixed inset-0 z-[9998] flex items-center justify-center bg-[rgba(15,23,42,0.45)] backdrop-blur-[10px] opacity-100 pointer-events-auto transition-opacity duration-200 ease-out dark:bg-[rgba(0,0,0,0.6)]"
    >
        <div
            class="relative mx-4 w-full max-w-[380px] rounded-[24px] border border-[var(--border-subtle)] bg-white px-[20px] py-[22px] pb-[16px] shadow-[0_24px_60px_rgba(15,23,42,0.35)] animate-[za-modal-pop-in_0.2s_ease-out] dark:border-gray-700 dark:bg-gray-800 dark:shadow-[0_24px_60px_rgba(0,0,0,0.5)] sm:rounded-[18px] sm:px-4 {{ $accentClass }}"
        >
            <button
                type="button"
                aria-label="بستن"
                data-flash-close
                class="absolute cursor-pointer top-[10px] end-[12px] border-0 bg-transparent text-[1.4rem] leading-none text-[var(--darker-text-muted)] transition-colors duration-150 ease hover:text-[var(--text-dark-gray)] dark:text-gray-400 dark:hover:text-gray-200"
            >
                &times;
            </button>

            <div class="mb-[1.25rem] flex justify-center">
                <div
                    class="flex h-[46px] w-[46px] items-center justify-center rounded-full bg-[linear-gradient(135deg,var(--brand-500),var(--brand-600))] text-[1.4rem] font-bold text-[var(--text-invert)] shadow-[0_10px_25px_rgba(244,63,94,0.6)] dark:bg-[linear-gradient(135deg,var(--brand-400),var(--brand-500))] dark:shadow-[0_10px_25px_25px_rgba(244,63,94,0.3)]"
                >
                    {{ $icon }}
                </div>
            </div>

            <div class="px-[6px] text-center">
                <h2 class="mb-[4px] text-[1.1rem] font-bold text-[var(--text-main)] dark:text-gray-100">
                    {{ $title }}
                </h2>

                <p class="m-0 text-[0.88rem] text-[var(--text-muted)] dark:text-gray-400">
                    {{ $message }}
                </p>
            </div>

            <div class="mt-4 flex justify-center gap-2">
                @if($actionUrl)
                    <a
                        href="{{ $actionUrl }}"
                        class="inline-flex items-center justify-center gap-[6px] rounded-full border border-transparent bg-white px-[14px] py-[7px] text-[0.86rem] font-medium text-[var(--text-main)] no-underline transition-transform duration-50 ease-out hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700"
                    >
                        {{ $actionLabel }}
                    </a>
                @endif

                <button
                    type="button"
                    data-flash-close
                    class="inline-flex cursor-pointer items-center justify-center gap-[6px] rounded-full border border-transparent bg-gray-100 px-[14px] py-[7px] text-[0.86rem] font-medium text-[var(--text-main)] transition-transform duration-50 ease-out hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                >
                    باشه
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const backdrop = document.querySelector('[data-flash-modal]');
            if (!backdrop) return;

            const closeButtons = backdrop.querySelectorAll('[data-flash-close]');

            function closeFlashModal() {
                backdrop.style.opacity = '0';
                backdrop.style.pointerEvents = 'none';

                setTimeout(() => {
                    backdrop.remove();
                }, 250);
            }

            backdrop.addEventListener('click', function (event) {
                if (event.target === backdrop || event.target.closest('[data-flash-close]')) {
                    closeFlashModal();
                }
            });

            closeButtons.forEach(button => {
                button.addEventListener('click', closeFlashModal);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeFlashModal();
                }
            });
        });
    </script>
@endif

<div
    id="confirm-backdrop"
    data-confirm-backdrop
    class="fixed inset-0 z-[9998] flex items-center justify-center bg-[rgba(15,23,42,0.45)] backdrop-blur-[10px] opacity-0 pointer-events-none transition-opacity duration-200 ease-out dark:bg-[rgba(0,0,0,0.6)]"
>
    <div
        class="relative mx-4 w-full max-w-[380px] rounded-[24px] border border-[var(--border-subtle)] bg-white px-[20px] py-[22px] pb-[16px] shadow-[0_24px_60px_rgba(15,23,42,0.35)] animate-[za-modal-pop-in_0.2s_ease-out] dark:border-gray-700 dark:bg-gray-800 dark:shadow-[0_24px_60px_rgba(0,0,0,0.5)] sm:rounded-[18px] sm:px-4"
    >
        <button
            type="button"
            data-confirm-cancel
            class="absolute top-[10px] end-[12px] border-0 bg-transparent text-[1.4rem] leading-none cursor-pointer text-[var(--darker-text-muted)] transition-colors duration-150 ease hover:text-[var(--text-dark-gray)] dark:text-gray-400 dark:hover:text-gray-200"
        >
            &times;
        </button>

        <div class="mb-[1.25rem] flex justify-center">
            <div
                class="flex h-[46px] w-[46px] items-center justify-center rounded-full bg-[linear-gradient(135deg,var(--brand-500),var(--brand-600))] text-[1.4rem] font-bold text-[var(--text-invert)] shadow-[0_10px_25px_rgba(244,63,94,0.6)] dark:bg-[linear-gradient(135deg,var(--brand-400),var(--brand-500))] dark:shadow-[0_10px_25px_rgba(244,63,94,0.3)]"
            >
                !
            </div>
        </div>

        <div class="px-[6px] text-center">
            <h2
                data-confirm-title
                class="mb-[4px] text-[1.1rem] font-bold text-[var(--text-main)] dark:text-gray-100"
            >
                تأیید عملیات
            </h2>

            <p
                data-confirm-text
                class="m-0 text-[0.88rem] text-[var(--text-muted)] dark:text-gray-400"
            >
                آیا مطمئن هستید؟
            </p>
        </div>

        <div class="mt-4 flex justify-center gap-2">
            <button
                type="button"
                data-confirm-cancel
                class="inline-flex cursor-pointer items-center justify-center gap-[6px] rounded-full border border-transparent bg-gray-100 px-[14px] py-[7px] text-[0.86rem] font-medium text-[var(--text-main)] transition-transform duration-50 ease-out hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
            >
                انصراف
            </button>

            <button
                type="button"
                data-confirm-submit
                class="inline-flex cursor-pointer items-center justify-center gap-[6px] rounded-full border border-transparent bg-[var(--brand-500)] px-[14px] py-[7px] text-[0.86rem] font-medium text-white transition-transform duration-50 ease-out hover:bg-[var(--brand-600)] dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
            >
                بله، ادامه
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let activeForm = null;

        const backdrop = document.querySelector('[data-confirm-backdrop]');
        if (!backdrop) return;

        const titleEl = backdrop.querySelector('[data-confirm-title]');
        const textEl = backdrop.querySelector('[data-confirm-text]');
        const submitBtn = backdrop.querySelector('[data-confirm-submit]');
        const cancelBtns = backdrop.querySelectorAll('[data-confirm-cancel]');

        function openModal({title, text}) {
            titleEl.textContent = title;
            textEl.textContent = text;

            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.classList.add('opacity-100', 'pointer-events-auto');

            setTimeout(() => submitBtn.focus(), 100);
        }

        function closeModal() {
            backdrop.classList.remove('opacity-100', 'pointer-events-auto');
            backdrop.classList.add('opacity-0', 'pointer-events-none');
            activeForm = null;
        }

        function submitForm() {
            if (!activeForm) return;
            activeForm.submit();
        }

        document.addEventListener('submit', event => {
            const form = event.target;

            if (!form.classList.contains('js-confirm')) {
                return;
            }

            event.preventDefault();
            activeForm = form;

            openModal({
                title: form.dataset.confirmTitle || 'آیا اطمینان دارید؟',
                text: form.dataset.confirmText || 'این عملیات قابل بازگشت نیست.',
            });
        });

        submitBtn.addEventListener('click', submitForm);

        cancelBtns.forEach(button => {
            button.addEventListener('click', closeModal);
        });

        backdrop.addEventListener('click', event => {
            if (event.target === backdrop) {
                closeModal();
            }
        });

        document.addEventListener('keydown', event => {
            const isOpen = backdrop.classList.contains('opacity-100');

            if (!isOpen) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                closeModal();
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                submitForm();
            }
        });
    });
</script>
