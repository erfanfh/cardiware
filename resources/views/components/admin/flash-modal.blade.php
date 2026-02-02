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
        class="fixed inset-0 bg-[rgba(15,23,42,0.45)] backdrop-blur-[10px] flex items-center justify-center z-[9998] transition-opacity duration-200 ease-out opacity-100 pointer-events-auto"
        data-modal-backdrop
        id="flash-modal-backdrop">
        <div
            class="relative w-full max-w-[380px] mx-4 rounded-[24px] px-[20px] py-[22px] pb-[16px] bg-white shadow-[0_24px_60px_rgba(15,23,42,0.35)] border border-[var(--border-subtle)] animate-[za-modal-pop-in_0.2s_ease-out] sm:rounded-[18px] sm:px-4 {{ $accentClass }}">
            <button type="button"
                    class="absolute top-[10px] end-[12px] border-0 bg-transparent text-[1.4rem] leading-none cursor-pointer text-[var(--darker-text-muted)] transition-colors duration-150 ease hover:text-[var(--text-dark-gray)]"
                    data-modal-close aria-label="بستن">
                &times;
            </button>

            <div class="flex justify-center mb-[1.25rem]">
                <div
                    class="w-[46px] h-[46px] rounded-full flex items-center justify-center bg-[linear-gradient(135deg,var(--brand-500),var(--brand-600))] text-[var(--text-invert)] text-[1.4rem] font-bold shadow-[0_10px_25px_rgba(244,63,94,0.6)]">
                    {{ $icon }}
                </div>
            </div>

            <div class="text-center py-0 px-[6px]">
                <h2 class="m-0 mb-[4px] text-[1.1rem] font-bold text-[var(--text-main)]">{{ $title }}</h2>
                <p class="m-0 text-[0.88rem] text-[var(--text-muted)]">{{ $message }}</p>
            </div>

            <div class="flex justify-center gap-2 mt-4">
                @if($actionUrl)
                    <a href="{{ $actionUrl }}"
                       class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-50 ease-out">
                        {{ $actionLabel }}
                    </a>
                @endif

                <button type="button"
                        class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-50 ease-out"
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

<div id="confirm-backdrop"
     class="fixed inset-0 bg-[rgba(15,23,42,0.45)] backdrop-blur-[10px] flex items-center justify-center z-[9998] transition-opacity duration-200 ease-out opacity-0 pointer-events-none"
     data-confirm-backdrop>
    <div
        class="relative w-full max-w-[380px] mx-4 rounded-[24px] px-[20px] py-[22px] pb-[16px] bg-white shadow-[0_24px_60px_rgba(15,23,42,0.35)] border border-[var(--border-subtle)] animate-[za-modal-pop-in_0.2s_ease-out] sm:rounded-[18px] sm:px-4">
        <button type="button"
                class="absolute top-[10px] end-[12px] border-0 bg-transparent text-[1.4rem] leading-none cursor-pointer text-[var(--darker-text-muted)] transition-colors duration-150 ease hover:text-[var(--text-dark-gray)]"
                data-confirm-cancel>
            &times;
        </button>

        <div class="flex justify-center mb-[1.25rem]">
            <div
                class="w-[46px] h-[46px] rounded-full flex items-center justify-center bg-[linear-gradient(135deg,var(--brand-500),var(--brand-600))] text-[var(--text-invert)] text-[1.4rem] font-bold shadow-[0_10px_25px_rgba(244,63,94,0.6)]">
                !
            </div>
        </div>

        <div class="text-center py-0 px-[6px]">
            <h2 class="m-0 mb-[4px] text-[1.1rem] font-bold text-[var(--text-main)]" data-confirm-title>
                تأیید عملیات
            </h2>
            <p class="m-0 text-[0.88rem] text-[var(--text-muted)]" data-confirm-text>
                آیا مطمئن هستید؟
            </p>
        </div>

        <div class="flex justify-center gap-2 mt-4">
            <button type="button"
                    class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-50 ease-out"
                    data-confirm-cancel>
                انصراف
            </button>

            <button type="button"
                    class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-50 ease-out"
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
            titleEl.textContent = title;
            textEl.textContent = text;
            backdrop.classList.remove('opacity-0 pointer-events-none');
            backdrop.classList.add('opacity-1 pointer-events-auto');
        }

        function closeModal() {
            backdrop.classList.remove('opacity-1 pointer-events-auto');
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
                text: form.dataset.confirmText
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
