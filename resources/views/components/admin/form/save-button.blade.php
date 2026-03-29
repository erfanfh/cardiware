@props([
    'label' => 'ذخیره',
])

<div class="w-full h-16"></div>

<footer
    class="fixed bottom-0 left-0 w-full z-10 bg-white dark:bg-gray-900 flex justify-end gap-[8px] mt-[20px] p-[14px] border-t border-t-gray-200 dark:border-t-gray-700">
    <button type="submit"
            class="bg-brand-500 text-white text-[0.86rem] inline-flex items-center justify-center rounded-full py-[7px] px-[14px] cursor-pointer hover:bg-brand-600 dark:hover:bg-brand-400 transition-transform shadow-[0_10px_25px_theme(colors.brand.300)]">
        {{ $label }}
    </button>
</footer>
