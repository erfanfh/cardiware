@props([
    'striped' => false,
    'hoverable' => true,
])

<div {{ $attributes->merge(['class' => 'admin-list-shell overflow-hidden rounded-2xl border border-slate-200 bg-white']) }}>
    <div class="admin-list-scroll overflow-x-auto">
        <table class="admin-data-table min-w-full text-sm text-slate-700">
            {{ $slot }}
        </table>
    </div>
</div>
