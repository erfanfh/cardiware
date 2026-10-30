<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            @if($hasBulkActions)
                <x-admin.list.th width="44px" align="center">
                    <x-admin.list.bulk-checkbox :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($storyIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($storyIds) . ')'"/>
                </x-admin.list.th>
            @endif
            <x-admin.list.th label="عنوان" sortable sortKey="title" :current-sort="request('sort', 'sort_order')" :current-direction="request('direction', 'asc')"/>
            <x-admin.list.th label="ترتیب" sortable sortKey="sort_order" :current-sort="request('sort', 'sort_order')" :current-direction="request('direction', 'asc')"/>
            <x-admin.list.th>مدت نمایش</x-admin.list.th>
            <x-admin.list.th>بازهٔ انتشار</x-admin.list.th>
            <x-admin.list.th>وضعیت</x-admin.list.th>
            <x-admin.list.th label="زمان ایجاد" sortable sortKey="created_at" :current-sort="request('sort', 'sort_order')" :current-direction="request('direction', 'asc')"/>
        </tr>
    </x-admin.list.thead>
    <x-admin.list.tbody>
        @forelse($stories as $story)
            <x-admin.list.tr ::class="{'bg-slate-50/90': isSelected({{ $story->id }})}">
                @if($hasBulkActions)
                    <x-admin.list.td align="center">
                        <x-admin.list.bulk-checkbox name="ids[]" :value="$story->id" x-bind:checked="isSelected({{ $story->id }})" x-on:change="toggleSelection({{ $story->id }})"/>
                    </x-admin.list.td>
                @endif
                <x-admin.list.td>
                    <div class="flex min-w-[220px] items-center gap-3">
                        @if($story->thumbnail?->url ?? $story->image?->url)
                            <img src="{{ $story->thumbnail?->url ?? $story->image?->url }}" alt="" class="h-12 w-12 shrink-0 rounded-full object-cover ring-2 ring-brand-200" loading="lazy">
                        @else
                            <span class="text-xs text-red-600">تصویر موجود نیست</span>
                        @endif
                        <div class="min-w-0">
                            @can('story.edit')
                                <a href="{{ route('admin.stories.edit', $story) }}" class="block font-bold text-slate-800 hover:text-brand-600">{{ $story->title }}</a>
                            @else
                                <span class="block font-bold text-slate-800">{{ $story->title }}</span>
                            @endcan
                            <span class="text-xs text-slate-400">#{{ $story->id }}</span>
                            <x-admin.list.row-actions>
                                @can('story.edit')
                                    <x-admin.list.action tag="a" href="{{ route('admin.stories.edit', $story) }}" variant="primary">ویرایش</x-admin.list.action>
                                @endcan
                                @can('story.delete')
                                    <x-admin.form method="delete" action="{{ route('admin.stories.destroy', $story) }}" data-confirm-title="حذف استوری" data-confirm-text="استوری «{{ $story->title }}» حذف شود؟">
                                        <x-admin.list.action tag="button" type="submit" variant="danger">حذف</x-admin.list.action>
                                    </x-admin.form>
                                @endcan
                            </x-admin.list.row-actions>
                        </div>
                    </div>
                </x-admin.list.td>
                <x-admin.list.td>{{ number_format($story->sort_order) }}</x-admin.list.td>
                <x-admin.list.td>{{ $story->duration_seconds }} ثانیه</x-admin.list.td>
                <x-admin.list.td>
                    <span class="block text-xs">شروع: {{ $story->starts_at ? jalali_date($story->starts_at, 'Y/m/d H:i') : 'بلافاصله' }}</span>
                    <span class="mt-1 block text-xs text-slate-500">پایان: {{ $story->ends_at ? jalali_date($story->ends_at, 'Y/m/d H:i') : 'بدون انقضا' }}</span>
                </x-admin.list.td>
                <x-admin.list.td><x-admin.badge :color="$story->displayStatus() === 'در حال نمایش' ? 'green' : 'slate'">{{ $story->displayStatus() }}</x-admin.badge></x-admin.list.td>
                <x-admin.list.td muted>{{ jalali_date($story->created_at, 'Y/m/d H:i') }}</x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty :colspan="$hasBulkActions ? 7 : 6" title="استوری‌ای پیدا نشد" description="فیلترها را تغییر دهید یا یک استوری جدید بسازید."
                :action-text="\Illuminate\Support\Facades\Gate::allows('story.create') ? 'ایجاد استوری' : null"
                :action-href="\Illuminate\Support\Facades\Gate::allows('story.create') ? route('admin.stories.create') : null"/>
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
