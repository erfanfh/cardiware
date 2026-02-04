@extends('admin.layout')

@section('title', 'مدیریت دسته‌بندی‌ها')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen">

        <div class="p-6 bg-gray-50 min-h-screen">
            <!-- نمایش پیام هشدار اگه اسلاگ تغییر کرده باشه -->
            @if(session('warning'))
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-lg">
                    <p class="font-medium">{{ session('warning') }}</p>
                </div>
            @endif

            <!-- نمایش پیام موفقیت -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-900">دسته‌بندی‌ها</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- create & edit form -->
            <div class="lg:col-span-4 order-2 lg:order-2 bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-3">
                    {{ $editingTerm ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی جدید' }}
                </h2>

                <!-- index form -->
                <form id="term-form"
                      action="{{ $editingTerm ? route('admin.taxonomy.terms.update', $editingTerm) : route('admin.taxonomy.terms.store') }}"
                      method="POST">
                    @csrf
                    @if($editingTerm)
                        @method('PUT')
                    @endif

                    <!-- change slug -->
                    <input type="hidden" name="slug_change_confirmed" id="slug-change-confirmed" value="0">

                    <!-- name -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">نام دسته‌بندی <span
                                class="text-red-600">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $editingTerm->title ?? '') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <input type="hidden" name="type" value="category">

                    <!-- slug -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسلاگ</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $editingTerm->slug ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- parent -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">دسته والد</label>
                        <select name="parent_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="">— بدون والد —</option>
                            @foreach ($terms as $p)
                                @if (!$editingTerm || $p->id !== $editingTerm->id)
                                    <option
                                        value="{{ $p->id }}" {{ old('parent_id', $editingTerm->parent_id ?? '') == $p->id ? 'selected' : '' }}>
                                        {{ str_repeat('— ', $p->level ?? 0) }}{{ $p->title }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- description -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات</label>
                        <textarea name="description" rows="5"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">{{ old('description', $editingTerm->description ?? '') }}</textarea>
                    </div>

                    <!-- buttons -->
                    <div class="flex gap-4">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium transition">
                            {{ $editingTerm ? 'بروزرسانی دسته‌بندی' : 'افزودن دسته‌بندی' }}
                        </button>

                        @if($editingTerm)
                            <a href="{{ route('admin.taxonomy.terms.index') }}"
                               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg font-medium text-center transition">
                                لغو ویرایش
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="lg:col-span-8 order-1 lg:order-1">
                <!-- group action -->
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
                    <form action="{{ route('admin.taxonomy.terms.bulk') }}" method="POST"
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

                    <form method="GET" action="{{ route('admin.taxonomy.terms.index') }}" class="flex-1">
                        <div class="flex gap-2">
                            <input type="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="جستجو در دسته‌بندی‌ها..."
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </form>
                </div>

                <!-- table -->
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
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">توضیحات
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">مسیر</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($tree as $term)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $term->id }}"
                                               class="rounded h-5 w-5">
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"
                                        style="padding-right: {{ ($term->level ?? 0) * 3 }}rem;">
                                        {{ $term->title }}
                                        @if ($term->children->isNotEmpty())
                                            <span class="text-xs text-gray-500 mr-2">(دارای زیرمجموعه)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $term->slug }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($term->description ?? '—', 60) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $term->path ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        <a href="{{ route('admin.taxonomy.terms.index', ['edit' => $term->id]) }}"
                                           class="text-blue-600 hover:text-blue-800 font-medium">ویرایش</a>
                                        <span class="text-gray-300">|</span>
                                        <form action="{{ route('admin.taxonomy.terms.destroy', $term) }}" method="POST"
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
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-lg">
                                        هنوز هیچ دسته‌بندی‌ای ایجاد نشده است.
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
            const form = document.getElementById('term-form');
            const slugInput = document.getElementById('slug');
            const confirmInput = document.getElementById('slug-change-confirmed');

            if (form && slugInput) {
                const originalSlug = '{{ $editingTerm->slug ?? '' }}';

                form.addEventListener('submit', function (e) {
                    const currentSlug = slugInput.value.trim();

                    if (originalSlug && currentSlug !== originalSlug) {
                        e.preventDefault();
                        window.confirmSlugChange(originalSlug, currentSlug, form, confirmInput);
                    }
                });
            }

            // ========== Bulk Delete Alert ==========
            const bulkForm = document.querySelector('form[action="{{ route("admin.taxonomy.terms.bulk") }}"]');

            if (bulkForm) {
                bulkForm.addEventListener('submit', function (e) {
                    const actionSelect = this.querySelector('select[name="action"]');
                    const selectedAction = actionSelect.value;

                    const checkedCheckboxes = document.querySelectorAll('input[name="ids[]"]:checked');
                    const checkedCount = checkedCheckboxes.length;

                    if (selectedAction === 'delete' && checkedCount > 0) {
                        e.preventDefault();
                        window.confirmBulkDelete(checkedCount, checkedCheckboxes, bulkForm);
                    } else if (selectedAction === 'delete' && checkedCount === 0) {
                        e.preventDefault();
                        window.showNoItemsSelected();
                    }
                });
            }
        });
    </script>
@endpush
