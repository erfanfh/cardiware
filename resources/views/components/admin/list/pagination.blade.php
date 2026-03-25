@props([
    'model'
])

<div>
    {{ $model->links() }}

    <div class="text-xs text-slate-500 text-center mt-3">
        نمایش
        <span class="font-bold text-slate-700">{{ $model->firstItem() }}</span>
        تا
        <span class="font-bold text-slate-700">{{ $model->lastItem() }}</span>
        از
        <span class="font-bold text-slate-700">{{ $model->total() }}</span>
        پروژه
    </div>
</div>
