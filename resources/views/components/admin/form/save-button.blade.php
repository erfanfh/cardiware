@props([
    'label' => 'ذخیره',
])

<div class="w-full h-16" aria-hidden="true"></div>

<footer
    class="admin-save-bar fixed bottom-0 left-0 w-full z-10 bg-white/90 backdrop-blur flex justify-end gap-[8px] mt-[20px] p-[14px] border-t border-t-slate-200">
    <button id="submit-btn" type="submit"
            class="bg-brand-500 text-white text-[0.86rem] inline-flex items-center justify-center rounded-full py-[7px] px-[14px] cursor-pointer hover:bg-brand-600 transition-transform shadow-[0_10px_25px_theme(colors.brand.300)]">
        {{ $label }}
    </button>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const button = document.getElementById('submit-btn');

        if (button) {
            const form = button.closest('form');

            if (form) {
                form.addEventListener('submit', () => {
                    button.disabled = true;
                    button.style.opacity = '0.5';
                    button.style.cursor = 'not-allowed';
                });
            }
        }
    });
</script>
