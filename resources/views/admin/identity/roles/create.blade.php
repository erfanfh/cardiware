@extends('admin.layout')

@section('title', 'ایجاد نقش جدید')

@section('page_header')
    <div class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--text-main)]">ایجاد نقش جدید</h1>
                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    ایجاد نقش جدید برای مدیریت دسترسی‌ها
                </p>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-lg border border-[var(--border-subtle)] overflow-hidden">
        <form method="POST" action="{{ route('admin.roles.store') }}" class="p-6">
            @csrf

            <!-- Role Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-[var(--text-main)] mb-2">
                    عنوان نقش <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    class="w-full px-4 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent @error('title') border-red-500 @enderror"
                    placeholder="مثلاً: مدرس"
                    required
                >
                @error('title')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Permissions -->
            <div class="mb-6">
                <h2 class="text-lg font-medium text-[var(--text-main)] mb-4">دسترسی‌ها</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($permissionGroups as $group => $groupPermissions)
                        <div class="border border-[var(--border-subtle)] rounded-lg p-4 bg-[var(--bg-surface)]">
                            <h3 class="font-medium text-[var(--text-main)] mb-3 text-sm">
                                {{ ucfirst($group) }}
                            </h3>
                            <div class="space-y-2">
                                @foreach($groupPermissions as $permission)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->id }}"
                                            class="w-4 h-4 rounded border-[var(--border-subtle)] text-[var(--brand-500)] focus:ring-[var(--brand-500)]"
                                            {{ old('permissions') && in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                        <span class="text-sm text-[var(--text-muted)]">{{ $permission->title }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('admin.roles.index') }}"
                   class="px-4 py-2 bg-[var(--bg-chip)] hover:bg-[var(--bg-hover)] text-[var(--text-muted)] rounded-lg font-medium transition-colors duration-150 ease-out">
                    انصراف
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-[var(--brand-500)] hover:bg-[var(--brand-600)] text-white rounded-lg font-medium transition-colors duration-150 ease-out">
                    ایجاد نقش
                </button>
            </div>
        </form>
    </div>
@endsection
