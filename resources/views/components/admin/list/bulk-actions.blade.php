@props([
    'name' => 'bulk_action',
    'placeholder' => 'کارهای دسته‌جمعی',
    'options' => [],
    'buttonText' => 'اجرا',
])

<div class="flex flex-wrap items-center gap-2">
    <div class="min-w-[190px]">
        <x-admin.form.select
            :name="$name"
            :options="$options"
            :value="request($name)"
        />
    </div>

    <button
        type="submit"
        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
    >
        {{ $buttonText }}
    </button>
</div>
