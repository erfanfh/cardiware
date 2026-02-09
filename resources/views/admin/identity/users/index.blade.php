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
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-lg border border-[var(--border-subtle)] overflow-hidden">
        <!-- Filters -->
        <div class="p-4 border-b border-[var(--border-subtle)] bg-[var(--bg-surface)]">
            <form method="GET" action="{{ route('admin.identity.users.index') }}" class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="sr-only">جستجو</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="جستجو بر اساس نام، نام خانوادگی یا ایمیل..."
                            class="w-full pl-10 pr-4 py-2 border border-[var(--border-subtle)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent text-sm"
                        >
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">
                            🔍
                        </div>
                    </div>
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
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">شناسه</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">نام کامل</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">ایمیل</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">نقش</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--text-muted)]">تاریخ عضویت</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-subtle)]">
                @forelse($users as $user)
                    <tr class="hover:bg-[var(--bg-hover)] transition-colors duration-150">
                        <td class="px-6 py-4 text-sm text-[var(--text-muted)] font-mono">{{ $user->id }}</td>

                        <td class="px-6 py-4">
                                <span class="font-medium text-[var(--text-main)]">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </span>
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

                        <td class="px-6 py-4 text-sm text-[var(--text-muted)]">
                            {{ $user->created_at->format('Y/m/d H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-[var(--text-muted)]">
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
