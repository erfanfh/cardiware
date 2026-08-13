@php
    $isEdit = isset($role) && $role;

    $groupedPermissions = \App\Domain\Identity\Models\Permission::query()
        ->orderBy('group')
        ->orderBy('label')
        ->get()
        ->groupBy('group');

    $selectedPermissionIds = old(
        'permissions',
        $isEdit ? $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all() : []
    );

    $groupLabels = [
        'admin' => 'پنل ادمین',
        'category' => 'دسته‌بندی‌ها',
        'tag' => 'برچسب‌ها',
        'post' => 'نوشته‌ها',
        'page' => 'برگه‌ها',
        'project' => 'پروژه‌ها',
        'role' => 'نقش‌ها',
        'user' => 'کاربران',
        'setting' => 'تنظیمات',
        'page-layout' => 'مدیریت چیدمان',
        'comment' => 'دیدگاه‌ها',
        'navigation-menu' => 'فهرست‌ها',
        'redirect' => 'ریدایرکت ها',
        'product' => 'محصولات',
        'order' => 'سفارش‌ها',
        'purchase-access' => 'دسترسی خرید و دانلود',
        'store' => 'فروشنده‌ها',
        'coupon' => 'کدهای تخفیف',
    ];
@endphp

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.metabox title="اطلاعات نقش">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin.field required label="عنوان">
                <x-admin.form.input
                    name="title"
                    :value="old('title', $role?->title)"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="دسترسی‌ها">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            @foreach($groupedPermissions as $group => $items)
                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">
                                    {{ $groupLabels[$group] ?? $group ?: '' }}
                                </h3>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $items->count() }} دسترسی در این گروه
                                </p>
                            </div>

                            <div
                                class="inline-flex items-center rounded-xl bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700">
                                {{ $items->count() }} مورد
                            </div>
                        </div>
                    </div>

                    <div class="p-4 space-y-3">
                        @foreach($items as $permission)
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50/40">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-slate-900">
                                            {{ $permission->label }}
                                        </div>

                                        @if(!empty($permission->key))
                                            <div class="mt-1 text-xs text-slate-500 ltr">
                                                {{ $permission->key }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="shrink-0">
                                        <x-admin.form.switch
                                            name="permissions[{{ $permission->id }}]"
                                            label="فعال"
                                            :checked="in_array((string) $permission->id, collect($selectedPermissionIds)->map(fn ($id) => (string) $id)->all(), true)"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </x-admin.metabox>
</x-admin.page>
