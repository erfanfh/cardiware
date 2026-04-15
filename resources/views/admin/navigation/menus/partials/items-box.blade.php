<div>
    <div x-show="countItems() === 0"
         class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
        <div
            class="mx-auto flex h-14 w-14 [&_svg]:w-6 [&_svg]:h-6 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm">
            @include('icons.right-to-left-list-bullet')
        </div>

        <h3 class="mt-4 text-base font-black text-slate-900">
            هنوز آیتمی برای این فهرست ثبت نشده
        </h3>

        <p class="mx-auto mt-2 max-w-lg text-sm leading-7 text-slate-500">
            از ستون سمت راست، آیتم موردنظر را انتخاب کنید تا به ساختار این فهرست اضافه
            شود.
        </p>
    </div>

    <div x-show="countItems() > 0" class="nav-builder-tree space-y-4" x-ref="treeFrame">
        <div id="tree-root" x-ref="treeRoot"></div>
    </div>
</div>
