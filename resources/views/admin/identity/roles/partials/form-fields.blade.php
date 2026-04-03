@php
    use App\Domain\Project\Enums\ProjectStatusEnum;
    $isEdit = isset($role) && $role;
@endphp

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <x-admin.field required label="عنوان">
            <x-admin.form.input
                name="title"
                :value="old('title', $role?->title)"
            />
        </x-admin.field>

        <x-admin.field required label="دسترسی‌ها">
            <x-admin.form.async-select
                name="permissions"
                :value="request('permissions')"
                :selected-options="$selectedPermissions"
                :search-url="route('admin.permissions.ajax-search')"
                :multiple="true"
                placeholder="جستجو در دسترسی‌ها..."
            />
        </x-admin.field>
    </div>
</x-admin.page>
