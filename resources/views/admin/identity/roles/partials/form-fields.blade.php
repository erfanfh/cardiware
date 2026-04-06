@php
    use App\Domain\Project\Enums\ProjectStatusEnum;
    $isEdit = isset($role) && $role;
@endphp

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <div class="grid grid-cols-12">
        <x-admin.field class="col-span-4" required label="عنوان">
            <x-admin.form.input
                name="title"
                :value="old('title', $role?->title)"
            />
        </x-admin.field>
        <div class="grid col-span-12 grid-cols-1 md:grid-cols-2 gap-2">
            @foreach(\App\Domain\Identity\Models\Permission::all()->groupBy('group') as $group => $items)
                <div class="mb-6">
                    <h3 class="font-bold text-sm mt-3 text-gray-700">
                        {{ $group }}
                    </h3>

                    @foreach($items as $permission)
                        <x-admin.field label="{{ $permission->label }}">
                            <x-admin.form.switch name="permissions[]"
                                                 value="{{ $permission->id }}"
                                                 label="فعال"
                                                 :checked="$role ? $permission->roles->contains($role) : false"
                            />
                        </x-admin.field>
                    @endforeach
                </div>
            @endforeach

        </div>
    </div>
</x-admin.page>
