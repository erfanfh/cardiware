<div>
    <div class="p-6 bg-gray-50 min-h-screen">

        @if (session()->has('warning'))
            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-lg">
                <p class="font-medium">{{ session('warning') }}</p>
            </div>
        @endif

        @if (session()->has('success'))
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
                    {{ $editingId ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی جدید' }}
                </h2>

                <!-- form -->
                <form wire:submit.prevent="{{ $editingId ? 'update' : 'store' }}">

                    <!-- name -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">نام دسته‌بندی <span
                                class="text-red-600">*</span></label>
                        <input type="text" wire:model.defer="title" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- slug -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسلاگ</label>
                        <input type="text" wire:model.defer="slug"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- parent -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">دسته والد</label>
                        <select wire:model.defer="parent_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded- lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="">— بدون والد —</option>
                            @foreach ($terms as $p)
                                @if (!$editingId || $p->id != $editingId)
                                    <option
                                        value="{{ $p->id }}" {{ $p->id == $parent_id ? 'selected' : '' }}>
                                        {{ str_repeat('— ', $p->level ?? 0) }}{{ $p->title }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- description -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات</label>
                        <textarea wire:model.defer="description" rows="5"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></textarea>
                    </div>

                    <!-- buttons -->
                    <div class="flex gap-4">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium transition">
                            {{ $editingId ? 'بروزرسانی دسته‌بندی' : 'افزودن دسته‌بندی' }}
                        </button>

                        @if($editingId)
                            <a href="{{ route('admin.categories.index') }}" wire:navigate
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
                    <form wire:submit.prevent="bulkAction" class="flex-1 flex gap-3 items-center">
                        <select wire:model="selectedAction"
                                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">عملیات گروهی</option>
                            <option value="delete">حذف</option>
                        </select>
                        <button wire:click="bulkDelete"
                                class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg text-sm">
                            اعمال
                        </button>
                    </form>

                    <div class="flex-1">
                        <input type="search" wire:model.live.debounce.300ms="search"
                               placeholder="جستجو در دسته‌بندی‌ها..."
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- table -->
                <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase w-10">
                                    <input type="checkbox" id="select-all" wire:model.live="selectAll"
                                           class="rounded h-5 w-5">
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">نام
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">اسلاگ
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">توضیحات
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">مسیر
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">
                                    عملیات
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($terms as $term)
                                <tr class="hover:bg-gray-50 transition-colors {{ $this->isSelected($term->id) ? 'bg-indigo-50' : '' }}">
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $term->id }}"
                                               class="rounded h-5 w-5 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    </td>

                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"
                                        style="padding-right: {{ ($term->level ?? 0) * 3 }}rem;">
                                        {{ $term->title }}
                                        @if (isset($term->children) && $term->children->isNotEmpty())
                                            <span class="text-xs text-gray-500 mr-2">(دارای {{ $term->children->count() }} زیرمجموعه)</span>
                                        @endif

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $term->slug }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($term->description ?? '—', 60) }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap text-sm text-gray-500">
                                        {{ $term->path }} {{-- اگر $term از دیتابیس لود شده و path دارد، این کار می کند --}}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        <button wire:click="edit({{ $term->id }})"
                                                class="text-blue-600 hover:text-blue-800 font-medium">ویرایش
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" wire:confirm="مطمئنی می‌خوای حذف کنی؟"
                                                wire:click="delete({{ $term->id }})"
                                                class="text-red-600 hover:text-red-800 font-medium">
                                            حذف
                                        </button>
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

                {{-- paginate --}}
                {{-- @if (!$search)
                    <div class="mt-4">
                        {{ $tree->links() }}
                    </div>
                @endif --}}
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // مدیریت سلکت باکس Select All
            document.addEventListener('DOMContentLoaded', function () {
                Livewire.on('refresh', () => {
                    document.getElementById('select-all').checked = false;
                });
            });

            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('component.updated', ({component, payload}) => {
                    if (component.name === 'admin.categories-table') {
                        const selectAllCheckbox = document.getElementById('select-all');
                        const checkboxes = Array.from(document.querySelectorAll('input[name="selectedIds[]"]'));

                        if (selectAllCheckbox && checkboxes.length > 0) {
                            const checkedCount = checkboxes.filter(cb => cb.checked).length;
                            selectAllCheckbox.checked = checkedCount === checkboxes.length && checkedCount > 0;
                        }
                    }
                });
            });
        </script>
    @endpush
</div>
