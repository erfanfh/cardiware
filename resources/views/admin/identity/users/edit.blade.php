@extends('admin.layout')

@section('title', 'ویرایش کاربر')

@section('page_header')
    <div class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--text-main)]">ویرایش کاربر</h1>
                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    ویرایش اطلاعات کاربر "{{ $user->first_name }} {{ $user->last_name }}"
                </p>
            </div>

            <a href="{{ route('admin.identity.users.index') }}"
               class="px-4 py-2 bg-[var(--bg-chip)] hover:bg-[var(--bg-hover)] text-[var(--text-muted)] rounded-lg font-medium transition-colors duration-150 ease-out text-sm">
                بازگشت به لیست کاربران
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg border border-[var(--border-subtle)] overflow-hidden">
            <form method="POST" action="{{ route('admin.identity.users.update', $user) }}" class="p-6">
                @csrf
                @method('PUT')

                <!-- Personal Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-[var(--text-main)] mb-4 pb-2 border-b border-[var(--border-subtle)]">
                        اطلاعات شخصی
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                                نام <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name', $user->first_name) }}"
                                class="w-full px-3 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm @error('first_name') border-red-500 @enderror"
                                placeholder="نام"
                            >
                            @error('first_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                                نام خانوادگی <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name', $user->last_name) }}"
                                class="w-full px-3 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm @error('last_name') border-red-500 @enderror"
                                placeholder="نام خانوادگی"
                            >
                            @error('last_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="display_name" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                                نام نمایشی
                            </label>
                            <input
                                type="text"
                                id="display_name"
                                name="display_name"
                                value="{{ old('display_name', $user->display_name) }}"
                                class="w-full px-3 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm @error('display_name') border-red-500 @enderror"
                                placeholder="نام نمایشی (اختیاری)"
                            >
                            @error('display_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-[var(--text-main)] mb-4 pb-2 border-b border-[var(--border-subtle)]">
                        اطلاعات تماس
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                                ایمیل <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                class="w-full px-3 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm @error('email') border-red-500 @enderror"
                                placeholder="example@email.com"
                            >
                            @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="mobile" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                                موبایل <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="mobile"
                                name="mobile"
                                value="{{ old('mobile', $user->mobile) }}"
                                class="w-full px-3 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm @error('mobile') border-red-500 @enderror"
                                placeholder="09xxxxxxxxx"
                            >
                            @error('mobile')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Role and Status -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-[var(--text-main)] mb-4 pb-2 border-b border-[var(--border-subtle)]">
                        نقش و وضعیت
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="role_id" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                                نقش <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="role_id"
                                name="role_id"
                                class="w-full px-3 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm bg-white @error('role_id') border-red-500 @enderror">
                                <option value="">انتخاب نقش</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="status"
                                    value="1"
                                    {{ old('status', $user->status->value === 'active') ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-[var(--border-subtle)] text-[var(--brand-500)] focus:ring-[var(--brand-500)]"
                                >
                                <span class="text-sm text-[var(--text-main)]">کاربر فعال باشد</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-[var(--text-main)] mb-4 pb-2 border-b border-[var(--border-subtle)]">
                        رمز عبور
                    </h3>

                    <div class="space-y-4">
                        <div class="relative">
                            <label for="password" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                                رمز عبور جدید (اختیاری)
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="w-full px-3 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm @error('password') border-red-500 @enderror"
                                    placeholder="برای تغییر رمز عبور وارد کنید"
                                >
                                <!-- دکمه نمایش/پنهان کردن رمز عبور -->
                                <button
                                    type="button"
                                    id="togglePassword"
                                    onclick="togglePasswordVisibility('password', 'togglePassword')"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors">
                                    👁️
                                </button>
                                <!-- دکمه کپی رمز عبور -->
                                <button
                                    type="button"
                                    onclick="copyPassword('password')"
                                    class="absolute left-10 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"
                                    title="کپی رمز عبور">
                                    📋
                                </button>
                                <!-- دکمه تولید رمز عبور -->
                                <button
                                    type="button"
                                    onclick="generatePassword()"
                                    class="absolute left-20 top-1/2 -translate-y-1/2 px-2 py-1 bg-[var(--brand-500)] hover:bg-[var(--brand-600)] text-white text-xs rounded text-sm">
                                    تولید رمز عبور
                                </button>
                            </div>
                            @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-[var(--text-muted)]">
                                اگر رمز عبور را تغییر نمی‌دهید، این فیلد را خالی بگذارید.
                            </p>
                        </div>

                        <div class="relative">
                            <label for="password_confirmation" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                                تکرار رمز عبور جدید
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="w-full px-3 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm @error('password_confirmation') border-red-500 @enderror"
                                    placeholder="تکرار رمز عبور"
                                >
                                <!-- دکمه نمایش/پنهان کردن رمز عبور -->
                                <button
                                    type="button"
                                    id="togglePasswordConfirmation"
                                    onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordConfirmation')"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors">
                                    👁️
                                </button>
                            </div>
                            @error('password_confirmation')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[var(--border-subtle)]">
                    <a href="{{ route('admin.identity.users.index') }}"
                       class="px-4 py-2 bg-[var(--bg-chip)] hover:bg-[var(--bg-hover)] text-[var(--text-muted)] rounded-lg font-medium transition-colors duration-150 ease-out text-sm">
                        انصراف
                    </a>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-[var(--brand-500)] hover:bg-[var(--brand-600)] text-white rounded-lg font-medium transition-colors duration-150 ease-out text-sm">
                        به‌روزرسانی کاربر
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);

            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }

        function generatePassword() {
            const length = 12;
            const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
            let password = '';

            password += charset.charAt(Math.floor(Math.random() * 26));
            password += charset.charAt(Math.floor(Math.random() * 26) + 26);
            password += charset.charAt(Math.floor(Math.random() * 10) + 52);
            password += charset.charAt(Math.floor(Math.random() * 8) + 62);

            for (let i = 4; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * charset.length);
                password += charset[randomIndex];
            }

            password = password.split('').sort(() => 0.5 - Math.random()).join('');

            document.getElementById('password').value = password;
            document.getElementById('password_confirmation').value = password;

            Swal.fire({
                title: '✓ رمز عبور تولید شد',
                html: `
                <div class="text-right text-sm">
                    <p class="mb-2 font-mono bg-gray-100 p-2 rounded">${password}</p>
                    <p class="text-gray-600">رمز عبور به صورت خودکار در فیلدها قرار گرفت.</p>
                </div>
            `,
                icon: 'success',
                confirmButtonText: 'کپی و بستن',
                confirmButtonColor: '#3085d6',
                customClass: {
                    popup: 'rtl-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    copyPassword('password');
                }
            });
        }

        function copyPassword(inputId) {
            const input = document.getElementById(inputId);
            input.select();
            input.setSelectionRange(0, 99999);

            try {
                navigator.clipboard.writeText(input.value).then(() => {
                    Swal.fire({
                        title: '✓ کپی شد',
                        text: 'رمز عبور در کلیپ‌بورد کپی شد.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'rtl-popup'
                        }
                    });
                });
            } catch (err) {
                document.execCommand('copy');
                Swal.fire({
                    title: '✓ کپی شد',
                    text: 'رمز عبور در کلیپ‌بورد کپی شد.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rtl-popup'
                    }
                });
            }
        }
    </script>
@endpush
