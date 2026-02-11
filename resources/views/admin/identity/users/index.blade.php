@php use App\Domain\Identity\Enums\UserStatusEnum; @endphp
@extends('admin.layout')

@section('title', 'لیست کاربران')

@section('page_header')
    <div class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--text-main)]">لیست کاربران</h1>
                <p class="mt-1 text-sm text-[var(--text-muted)]">
                    مدیریت کاربران سامانه
                </p>
            </div>

            <a href="{{ route('admin.identity.users.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[var(--brand-500)] hover:bg-[var(--brand-600)] text-white rounded-lg font-medium transition-colors duration-150 ease-out shadow-sm">
                <span>ایجاد کاربر جدید</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-lg border border-[var(--border-subtle)] overflow-hidden">
        <!-- Filters -->
        <div class="p-4 border-b border-[var(--border-subtle)] bg-[var(--bg-surface)]">
            <form method="GET" action="{{ route('admin.identity.users.index') }}"
                  class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="sr-only">جستجو</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="جستجو بر اساس نام، نام خانوادگی، نام نمایشی، ایمیل یا موبایل..."
                            class="w-full pl-10 pr-4 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm"
                        >
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">
                            🔍
                        </div>
                    </div>
                </div>

                <div class="min-w-[200px]">
                    <label for="role_id" class="sr-only">نقش</label>
                    <select
                        id="role_id"
                        name="role_id"
                        class="w-full pl-3 pr-10 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm bg-white">
                        <option value="">همه نقش‌ها</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-[var(--brand-500)] hover:bg-[var(--brand-600)] text-white rounded-lg font-medium transition-colors duration-150 ease-out text-sm">
                        اعمال فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'role_id']))
                        <a href="{{ route('admin.identity.users.index') }}"
                           class="px-4 py-2 bg-[var(--bg-chip)] hover:bg-[var(--bg-hover)] text-[var(--text-muted)] rounded-lg font-medium transition-colors duration-150 ease-out text-sm">
                            پاک کردن فیلترها
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[var(--bg-surface)] border-b border-[var(--border-subtle)]">
                <tr>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">تصویر</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">نام نمایشی</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">نام کامل</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">موبایل</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">ایمیل</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">نقش</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">نوشته‌ها</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">تغییر وضعیت کاربر</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">ویرایش</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                @forelse($users as $user)
                    <tr class="hover:bg-[var(--bg-hover)] transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div
                                class="w-10 h-10 rounded-full bg-[var(--bg-chip)] flex items-center justify-center text-[var(--text-muted)]">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                                <span class="font-medium text-[var(--text-main)]">
                                    {{ $user->display_name ?? '—' }}
                                </span>
                        </td>

                        <td class="px-6 py-4">
                                <span class="text-sm text-[var(--text-muted)]">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </span>
                        </td>

                        <td class="px-6 py-4 text-sm text-[var(--text-muted)] font-mono">
                            {{ $user->mobile ?? '—' }}
                        </td>

                        <td class="px-6 py-4 text-sm text-[var(--text-muted)]">
                            {{ $user->email }}
                        </td>

                        <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($user->role->is_default)
                                        bg-blue-100 text-blue-800
                                    @elseif($user->role->is_super_admin)
                                        bg-purple-100 text-purple-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $user->role->title }}
                                </span>
                        </td>

                        <td class="px-6 py-4">
                            <a href="#"
                               class="inline-flex items-center gap-1 text-sm font-medium text-[var(--brand-500)] hover:text-[var(--brand-600)] hover:underline transition-colors duration-150">
                                <span>0</span>
                                <span>نوشته</span>
                            </a>
                        </td>

                        <td class="px-15 py-4">
                            <div class="flex items-center">
                                @if($user->role->is_super_admin)
                                    <div class="relative inline-flex items-center cursor-not-allowed group">
                                        <div class="w-10 h-5 bg-gray-200 rounded-full transition-colors duration-200">
                                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                                        </div>
                                        <!-- پیام هنگام موس‌اور (با ::after) -->
                                        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            <div class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded-lg whitespace-nowrap">
                                                کاربر سوپر ادمین قابل غیرفعال‌سازی نیست
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <form
                                        action="{{ route('admin.identity.users.update', $user) }}"
                                        method="POST"
                                        class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status"
                                               value="{{ $user->status === UserStatusEnum::ACTIVE ? '0' : '1' }}">


                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="status-toggle"
                                                value="1"
                                                {{ $user->status === UserStatusEnum::ACTIVE ? 'checked' : '' }}
                                                class="sr-only peer"
                                            >
                                            <div
                                                class="w-10 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-0 rounded-full peer peer-checked:bg-blue-600 transition-colors duration-200">
                                                <span
                                                    class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></span>
                                            </div>
                                        </label>
                                    </form>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.identity.users.edit', $user) }}"
                                   class="p-2 text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--bg-hover)] rounded-lg transition-colors duration-150"
                                   title="ویرایش کاربر">
                                    ✎
                                </a>

                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-[var(--text-muted)]">
                            <div class="flex flex-col items-center gap-2">
                                <div class="text-4xl">📭</div>
                                <p class="text-sm">هیچ کاربری یافت نشد.</p>
                                @if(request()->hasAny(['search', 'role_id']))
                                    <a href="{{ route('admin.identity.users.index') }}"
                                       class="mt-2 text-sm text-[var(--brand-500)] hover:text-[var(--brand-600)] hover:underline">
                                        نمایش همه کاربران
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-[var(--border-subtle)] bg-[var(--bg-surface)]">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleSwitches = document.querySelectorAll('input[name="status-toggle"]');

            toggleSwitches.forEach(switchElement => {
                switchElement.addEventListener('change', function () {
                    this.closest('form').requestSubmit();
                });
            });
        });
    </script>
@endpush

@push('styles')

    <style>
        .page-item.active .page-link {
            @apply bg-[var(--brand-500)] text-white border-[var(--brand-500)];
        }

        .page-link {
            @apply text-[var(--text-main)] hover:bg-[var(--bg-hover)] border-[var(--border-subtle)];
        }
    </style>
@endpush
