<x-admin.list.quick-edit-row :row-id="$user->id" colspan="7">
    <div class="space-y-5">
        <x-admin.form
            method="PATCH"
            action="{{ route('admin.users.quick-update', $user) }}"
            class="space-y-5"
            x-on:submit.prevent="submitQuickEdit($event.target, {{ $user->id }})"
        >
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-800">
                            ویرایش سریع کاربر
                        </h3>

                        <x-admin.badge color="blue">
                            شناسه: {{ $user->id }}
                        </x-admin.badge>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.button
                        type="submit"
                        variant="solid"
                        size="sm"
                        ::disabled="isSaving({{ $user->id }})"
                    >
                        <span x-show="!isSaving({{ $user->id }})">ذخیره تغییرات</span>
                        <span x-show="isSaving({{ $user->id }})" x-cloak>در حال ذخیره...</span>
                    </x-admin.button>

                    <x-admin.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        x-on:click="close()"
                        ::disabled="isSaving({{ $user->id }})"
                    >
                        انصراف
                    </x-admin.button>
                </div>
            </div>

            <x-admin.form.ajax-error-box
                :row-id="$user->id"
                :fields="['first_name', 'last_name', 'status']"
            />

            <div class="grid grid-cols-12 gap-5">
                <div class="grid col-span-12 lg:col-span-6 grid-cols-2 gap-5">
                    <x-admin.field label="نام کاربر" class="col-span-2">
                        <x-admin.form.input
                            name="first_name"
                            :value="old('first_name', $user->first_name)"
                        />
                    </x-admin.field>

                    <x-admin.field label="نام خانوادگی کاربر" class="col-span-2">
                        <x-admin.form.input
                            name="last_name"
                            :value="old('last_name', $user->last_name)"
                        />
                    </x-admin.field>

                    <x-admin.field label="وضعیت">
                        <x-admin.form.select
                            name="status"
                            :options="$statuses"
                            :value="old('status', $user->status?->value)"
                        />
                    </x-admin.field>
                </div>
            </div>
        </x-admin.form>
    </div>
</x-admin.list.quick-edit-row>
