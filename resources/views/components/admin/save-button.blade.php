@props([
    'label' => 'ذخیره',
])

<footer
    class="fixed bottom-0 left-0 w-full z-10 bg-white dark:bg-gray-900 flex justify-end gap-[8px] mt-[20px] p-[14px] border-t border-t-[var(--border-subtle)] dark:border-t-gray-700">
    <button type="submit"
            class="bg-[var(--brand-500)] text-white text-[0.86rem] inline-flex items-center justify-center rounded-full py-[7px] px-[14px] cursor-pointer hover:bg-[var(--brand-600)] dark:hover:bg-[var(--brand-400)] transition-transform"
            style="box-shadow: 0 10px 25px var(--brand-300);">
        {{ $label }}
    </button>
</footer>
