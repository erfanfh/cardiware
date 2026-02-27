<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Flash Messages -->
    @if(session()->has('warning'))
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-lg">
            <p class="font-medium">{{ session('warning') }}</p>
        </div>
    @endif

    @if(session()->has('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">برچسب‌ها</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Form -->
        <div class="lg:col-span-4 order-2 lg:order-2 bg-white p-6 rounded-xl shadow-md border border-gray-200">
            <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-3">
                {{ $editingId ? 'ویرایش برچسب' : 'افزودن برچسب جدید' }}
            </h2>

            <form wire:submit="save">
                <!-- Title -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        نام برچسب <span class="text-red-600">*</span>
                    </label>
                    <input type="text" wire:model="title" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Slug -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسلاگ</label>
                    <input type="text" wire:model="slug"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات</label>
                    <textarea wire:model="description" rows="5"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4">
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium transition">
                        {{ $editingId ? 'بروزرسانی' : 'افزودن' }}
                    </button>
                    @if($editingId)
                        <button type="button" wire:click="cancelEdit"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg font-medium transition">
                            لغو
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="lg:col-span-8 order-1 lg:order-1">
            <!-- Bulk Actions & Search -->
            <div class="mb-6 flex flex-col sm:flex-row gap-4">
                <div class="flex gap-3 items-center">
                    <select wire:model="bulkAction"
                            class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
                        <option value="">عملیات گروهی</option>
                        <option value="delete">حذف</option>
                    </select>
                    <button wire:click="bulkDelete"
                            class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg text-sm">
                        اعمال
                    </button>
                </div>

                <input type="search" wire:model.live.debounce.300ms="search"
                       placeholder="جستجو..."
                       class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow border overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-right w-10">
                            <input type="checkbox" wire:click="toggleSelectAll($event.target.checked)" class="rounded">
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">نام</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">اسلاگ</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">توضیحات</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @forelse($this->tags as $tag)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" wire:click="toggleSelect({{ $tag->id }})"
                                       @checked(in_array($tag->id, $selectedIds)) class="rounded">
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $tag->title }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $tag->slug }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ \Str::limit($tag->description, 60) }}</td>
                            <td class="px-6 py-4 text-sm space-x-3">
                                <button wire:click="edit({{ $tag->id }})" class="text-blue-600 hover:text-blue-800">ویرایش</button>
                                <span class="text-gray-300">|</span>
                                <button wire:click="delete({{ $tag->id }})"
                                        wire:confirm="مطمئنی می‌خوای حذف کنی؟"
                                        class="text-red-600 hover:text-red-800">حذف</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                هنوز هیچ برچسبی ایجاد نشده است.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Slug Change Warning Modal -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('showSlugWarning', ({ oldSlug, newSlug }) => {
                if (confirm(`اسلاگ از '${oldSlug}' به '${newSlug}' تغییر می‌کند. ادامه می‌دهید؟`)) {
                @this.call('proceedWithSlugChange');
                }
            });
        });
    </script>
</div>
