<div class="min-w-0">
    <label for="{{ $id }}" class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">
        {{ $label }}
    </label>

    <input
        id="{{ $id }}"
        type="password"
        wire:model.defer="{{ $field }}"
        autocomplete="new-password"
        placeholder="{{ $placeholder }}"
        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold outline-none transition focus:ring-4
        @error($field)
            border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:ring-red-500/20
        @else
            border-slate-200 bg-slate-50 text-slate-800 placeholder:text-slate-400 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
        @enderror"
    >

    @error($field)
        <div class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
    @enderror
</div>
