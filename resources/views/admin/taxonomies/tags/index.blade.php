@extends('admin.layout')

@section('title', 'مدیریت برچسب‌ها')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen">
        <!-- Flash Messages -->
        @if(session('warning'))
            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-lg">
                <p class="font-medium">{{ session('warning') }}</p>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg">
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-900">برچسب‌ها</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Create & Edit Form -->
            <div class="lg:col-span-4 order-2 lg:order-2 bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-3">
                    {{ $editingTerm ? 'ویرایش برچسب' : 'افزودن برچسب جدید' }}
                </h2>

                <form id="tag-form"
                      action="{{ $editingTerm ? route('admin.taxonomies.tags.update', $editingTerm) : route('admin.taxonomies.tags.store') }}"
                      method="POST">
                    @csrf
                    @if($editingTerm)
                        @method('PUT')
                    @endif

                    <!-- Slug Change Confirmation -->
                    <input type="hidden" name="slug_change_confirmed" id="slug-change-confirmed" value="0">

                    <!-- Title -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            نام برچسب <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $editingTerm->title ?? '') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Slug -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسلاگ</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $editingTerm->slug ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات</label>
                        <textarea name="description" rows="5"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">{{ old('description', $editingTerm->description ?? '') }}</textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium transition">
                            {{ $editingTerm ? 'بروزرسانی برچسب' : 'افزودن برچسب' }}
                        </button>

                        @if($editingTerm)
                            <a href="{{ route('admin.taxonomies.tags.index') }}"
                               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg font-medium text-center transition">
                                لغو ویرایش
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="lg:col-span-8 order-1 lg:order-1">
                <!-- Bulk Actions -->
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
                    <form action="{{ route('admin.taxonomies.tags.bulk') }}" method="POST"
                          class="flex-1 flex gap-3 items-center">
                        @csrf
                        <select name="action"
                                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">عملیات گروهی</option>
                            <option value="delete">حذف</option>
                        </select>
                        <button type="submit"
                                class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                            اعمال
                        </button>
                    </form>

                    <form method="GET" action="{{ route('admin.taxonomies.tags.index') }}" class="flex-1">
                        <div class="flex gap-2">
                            <input type="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="جستجو در برچسب‌ها..."
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase w-10">
                                    <input type="checkbox" id="select-all" class="rounded h-5 w-5">
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">نام</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">اسلاگ</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">توضیحات</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($tags as $tag)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $tag->id }}"
                                               class="rounded h-5 w-5">
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $tag->title }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $tag->slug }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($tag->description ?? '—', 60) }}</td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        <a href="{{ route('admin.taxonomies.tags.index', ['edit' => $tag->id]) }}"
                                           class="text-blue-600 hover:text-blue-800 font-medium">ویرایش</a>
                                        <span class="text-gray-300">|</span>
                                        <form action="{{ route('admin.taxonomies.tags.destroy', $tag) }}" method="POST"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('مطمئنی می‌خوای حذف کنی؟')"
                                                    class="text-red-600 hover:text-red-800 font-medium">
                                                حذف
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-lg">
                                        هنوز هیچ برچسبی ایجاد نشده است.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ========== Slug Change Alert ==========
            const form = document.getElementById('tag-form');
            const slugInput = document.getElementById('slug');
            const confirmInput = document.getElementById('slug-change-confirmed');

            if (form && slugInput) {
                const originalSlug = '{{ $editingTerm->slug ?? '' }}';

                form.addEventListener('submit', function (e) {
                    const currentSlug = slugInput.value.trim();

                    if (originalSlug && currentSlug !== originalSlug) {
                        e.preventDefault();
                        window.confirmSlugChange(originalSlug, currentSlug, form, confirmInput, 'tag');
                    }
                });
            }

            // ========== Bulk Delete Alert ==========
            const bulkForm = document.querySelector('form[action="{{ route("admin.taxonomies.tags.bulk") }}"]');

            if (bulkForm) {
                bulkForm.addEventListener('submit', function (e) {
                    const actionSelect = this.querySelector('select[name="action"]');
                    const selectedAction = actionSelect.value;
                    const checkedCheckboxes = document.querySelectorAll('input[name="ids[]"]:checked');
                    const checkedCount = checkedCheckboxes.length;

                    if (selectedAction === 'delete' && checkedCount > 0) {
                        e.preventDefault();
                        window.confirmBulkDelete(checkedCount, checkedCheckboxes, bulkForm, 'tag');
                    } else if (selectedAction === 'delete' && checkedCount === 0) {
                        e.preventDefault();
                        window.showNoItemsSelected('tag');
                    }
                });
            }
        });
    </script>
@endpush
