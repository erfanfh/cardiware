@extends('admin.layout')

@section('title', 'انتساب نقش جدید')

@section('page_header')
    <div class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--text-main)]">انتساب نقش جدید</h1>
            </div>

            <a href="{{ route('admin.identity.roles.index') }}"
               class="px-4 py-2 bg-[var(--bg-chip)] hover:bg-[var(--bg-hover)] text-[var(--text-muted)] rounded-lg font-medium transition-colors duration-150 ease-out text-sm">
                بازگشت به لیست نقش‌ها
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg border border-[var(--border-subtle)] overflow-hidden">
            <div class="p-6 border-b border-[var(--border-subtle)] bg-[var(--bg-surface)]">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <span class="text-2xl">❕</span>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-[var(--text-main)] mb-2">
                            هشدار: این نقش کاربر دارد
                        </h2>
                        <p class="text-sm text-[var(--text-muted)]">
                            نقش "<span class="font-medium text-[var(--text-main)]">{{ $role->title }}</span>"
                            دارای <span class="font-bold text-red-600">{{ $userCount }}</span> کاربر است.
                        </p>
                        <p class="text-sm text-[var(--text-muted)] mt-2">
                            قبل از حذف این نقش، باید یک نقش جدید برای این کاربران انتخاب کنید.
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.identity.roles.assign-new-role', $role) }}" class="p-6">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-medium text-[var(--text-main)] mb-2">
                        نقش جدید برای کاربران <span class="text-red-500">*</span>
                    </label>

                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                        @forelse($availableRoles as $availableRole)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-[var(--border-subtle)] hover:border-[var(--brand-500)] hover:bg-[var(--bg-hover)] cursor-pointer transition-all duration-150">
                                <input
                                    type="radio"
                                    name="new_role_id"
                                    value="{{ $availableRole->id }}"
                                    id="role_{{ $availableRole->id }}"
                                    class="w-4 h-4 text-[var(--brand-500)] focus:ring-[var(--brand-500)]"
                                    {{ old('new_role_id') == $availableRole->id ? 'checked' : '' }}>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-[var(--text-main)]">{{ $availableRole->title }}</span>

                                        @if($availableRole->is_default)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                پیش‌فرض
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-[var(--text-muted)] mt-1">
                                        {{ $availableRole->users_count }} کاربر
                                    </p>
                                </div>
                            </label>
                        @empty
                            <div class="text-center py-8 text-[var(--text-muted)]">
                                هیچ نقش دیگری برای انتخاب وجود ندارد.
                            </div>
                        @endforelse
                    </div>

                    @error('new_role_id')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[var(--border-subtle)]">
                    <a href="{{ route('admin.identity.roles.index') }}"
                       class="px-4 py-2 bg-[var(--bg-chip)] hover:bg-[var(--bg-hover)] text-[var(--text-muted)] rounded-lg font-medium transition-colors duration-150 ease-out text-sm">
                        انصراف
                    </a>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-[var(--brand-500)] hover:bg-[var(--brand-600)] text-white rounded-lg font-medium transition-colors duration-150 ease-out text-sm flex items-center gap-2">
                        <span>✓</span>
                        <span>تأیید و حذف نقش</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .rtl-popup {
            direction: rtl;
            text-align: right;
        }

        .rtl-popup .swal2-title {
            text-align: right;
        }

        .rtl-popup .swal2-html-container {
            text-align: right;
        }
    </style>
@endpush
