@php
    use App\Domain\Identity\Enums\UserStatusEnum;
    $statuses = [];
    foreach (UserStatusEnum::cases() as $status){
        $statuses[$status->value] = $status->label();
    }
@endphp

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.metabox title="اطلاعات شخصی">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex flex-col gap-4">
                <x-admin.field required label="نام کاربری">
                    <x-admin.form.input
                        name="username"
                        :value="old('username', $user?->username)"
                    />
                </x-admin.field>

                <x-admin.field required label="نام">
                    <x-admin.form.input
                        name="first_name"
                        :value="old('first_name', $user?->first_name)"
                    />
                </x-admin.field>

                <x-admin.field required label="نام خانوادگی">
                    <x-admin.form.input
                        name="last_name"
                        :value="old('last_name', $user?->last_name)"
                    />
                </x-admin.field>

                <x-admin.field label="نام نمایشی">
                    <x-admin.form.input
                        name="display_name"
                        :value="old('display_name', $user?->display_name)"
                    />
                </x-admin.field>
            </div>

            <x-admin.field label="تصویر">
                <x-admin.form.media-picker
                    mode="field"
                    name="avatar"
                    :value="[
                    'id' => old('avatar.id', $user?->avatar_id),
                    'path' => old('avatar.path', $user?->avatar?->url),
                ]"
                    layout="vertical"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="اطلاعات تماس">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin.field required label="ایمیل">
                <x-admin.form.input
                    dir="ltr"
                    name="email"
                    :value="old('email', $user?->email)"
                />
            </x-admin.field>

            <x-admin.field required label="موبایل">
                <x-admin.form.input
                    dir="ltr"
                    name="mobile"
                    :value="old('mobile', $user?->mobile)"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="نقش و وضعیت">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin.field required label="نقش">
                <x-admin.form.select
                    name="role"
                    placeholder="انتخاب نقش"
                    :options="\App\Domain\Identity\Models\Role::pluck('title', 'id')->all()"
                    :value="old('role', $user->role_id ?? app(\App\Application\Identity\Actions\GetDefaultRoleAction::class)->execute()->id)"
                />
            </x-admin.field>
            <x-admin.field required label="وضعیت">
                <x-admin.form.select
                    name="status"
                    placeholder="انتخاب وضعیت"
                    :options="$statuses"
                    :value="old('status', $user->status->value ?? UserStatusEnum::ACTIVE->value)"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="امنیت">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-admin.field required label="رمز عبور">
                <x-admin.form.secret-input
                    id="password"
                    type="password"
                    dir="ltr"
                    name="password"
                />
            </x-admin.field>

            <x-admin.field required label="تکرار رمز عبور">
                <x-admin.form.secret-input
                    id="password_confirmation"
                    toggle="password_confirmation"
                    type="password"
                    dir="ltr"
                    name="password_confirmation"
                />
            </x-admin.field>
            <div>
                <x-admin.form.button onclick="generatePassword()" variant="dark" size="sm">
                    تولید رمز عبور
                </x-admin.form.button>
            </div>
        </div>
    </x-admin.metabox>
</x-admin.page>
