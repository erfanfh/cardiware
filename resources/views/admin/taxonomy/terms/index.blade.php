@extends('admin.layout')

@section('title', 'مدیریت دسته‌بندی‌ها')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
    <div class="p-6 bg-gray-50 min-h-screen">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ========== checkbox management (select all) ==========
            const selectAllCheckbox = document.getElementById('select-all');
            const itemCheckboxes = document.querySelectorAll('input[name="ids[]"]');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    itemCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }

            // when on of the checkbox changed
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);

                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                });
            });

            // ========== change slug alert (editing)  ==========
            const form = document.getElementById('term-form');
            const slugInput = document.getElementById('slug');
            const confirmInput = document.getElementById('slug-change-confirmed');

            if (form && slugInput) {
                const originalSlug = '{{ $editingTerm->slug ?? '' }}';

                form.addEventListener('submit', function (e) {
                    const currentSlug = slugInput.value.trim();

                    if (originalSlug && currentSlug !== originalSlug) {
                        e.preventDefault();

                        Swal.fire({
                            title: '⚠️ تأیید تغییر اسلاگ',
                            html: `
                                <div class="text-right">
                                    <p class="mb-3">آیا مطمئن هستید که می‌خواهید اسلاگ را تغییر دهید؟</p>
                                    <p class="font-medium mb-2">اسلاگ قبلی: <span class="text-gray-600">${originalSlug}</span></p>
                                    <p class="font-medium mb-3">اسلاگ جدید: <span class="text-blue-600">${currentSlug}</span></p>
                                    <p class="text-sm text-red-600">
                                        ⚠️ تغییر اسلاگ باعث تغییر آدرس این دسته‌بندی و تمام زیرمجموعه‌های آن می‌شود.<br>
                                        ممکن است لینک‌های قدیمی شکسته شوند و روی سئو تأثیر بگذارد.
                                    </p>
                                </div>
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'بله، تغییر بده',
                            cancelButtonText: 'لغو',
                            reverseButtons: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            customClass: {
                                popup: 'rtl-popup'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                confirmInput.value = '1';
                                form.submit();
                            }
                        });
                    }
                });
            }

            // ========== delete group action ==========
            const bulkForm = document.querySelector('form[action="{{ route("admin.taxonomy.terms.bulk") }}"]');

            if (bulkForm) {
                bulkForm.addEventListener('submit', function (e) {
                    const actionSelect = this.querySelector('select[name="action"]');
                    const selectedAction = actionSelect.value;

                    // find selected checkboxes
                    const checkedCheckboxes = document.querySelectorAll('input[name="ids[]"]:checked');
                    const checkedCount = checkedCheckboxes.length;

                    // if a checkbox selected to deleting
                    if (selectedAction === 'delete' && checkedCount > 0) {
                        e.preventDefault();

                        Swal.fire({
                            title: '⚠️ تأیید حذف گروهی',
                            html: `
                    <div class="text-right">
                        <p class="mb-3">آیا مطمئن هستید که می‌خواهید <strong>${checkedCount}</strong> دسته‌بندی انتخاب‌شده را حذف کنید؟</p>
                        <p class="text-sm text-red-600">
                            ⚠️ این عملیات غیرقابل بازگشت است!<br>
                            تمام دسته‌بندی‌های انتخاب‌شده به همراه زیرمجموعه‌هایشان حذف خواهند شد.
                        </p>
                    </div>
                `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'بله، حذف کن',
                            cancelButtonText: 'لغو',
                            reverseButtons: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            customClass: {
                                popup: 'rtl-popup'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // dynamic
                                checkedCheckboxes.forEach(checkbox => {
                                    const hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = 'ids[]';
                                    hiddenInput.value = checkbox.value;
                                    bulkForm.appendChild(hiddenInput);
                                });

                                bulkForm.submit();
                            }
                        });
                    }
                    // if no item
                    else if (selectedAction === 'delete' && checkedCount === 0) {
                        e.preventDefault();

                        Swal.fire({
                            title: '❌ هشدار',
                            text: 'لطفاً حداقل یک دسته‌بندی انتخاب کنید.',
                            icon: 'warning',
                            confirmButtonColor: '#3085d6',
                            customClass: {
                                popup: 'rtl-popup'
                            }
                        });
                    }
                });
            }
        });
    </script>
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

        .rtl-popup .swal2-actions {
            text-align: center;
        }
    </style>
@endpush
