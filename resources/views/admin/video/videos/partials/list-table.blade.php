<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($videoIds) . ')'" :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($videoIds) . ')'"/>
            </x-admin.list.th>
            <x-admin.list.th width="110px">کاور</x-admin.list.th>
            <x-admin.list.th>عنوان</x-admin.list.th>
            <x-admin.list.th>نویسنده</x-admin.list.th>
            <x-admin.list.th width="110px">منبع</x-admin.list.th>
            <x-admin.list.th width="120px">وضعیت</x-admin.list.th>
            <x-admin.list.th>دسته‌بندی‌ها</x-admin.list.th>
            <x-admin.list.th>دیدگاه‌ها</x-admin.list.th>
            <x-admin.list.th>بازدیدها</x-admin.list.th>
            <x-admin.list.th>امتیاز سئو</x-admin.list.th>
            <x-admin.list.th width="130px" label="زمان انتشار" sortable sortKey="published_at" :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($videos as $video)
            <x-admin.list.tr ::class="{ 'bg-slate-50/90': isSelected({{ $video->id }}) }">
                <x-admin.list.td align="center">
                    <x-admin.list.bulk-checkbox name="ids[]" :value="$video->id" x-bind:checked="isSelected({{ $video->id }})" x-on:change="toggleSelection({{ $video->id }})"/>
                </x-admin.list.td>
                <x-admin.list.td><x-admin.list.thumbnail :src="$video->cover?->url" :alt="$video->title" size="md"/></x-admin.list.td>
                <x-admin.list.td>
                    <div class="min-w-[280px]">
                        <a href="{{ $video->url }}" target="_blank" class="inline-block text-sm font-bold leading-6 text-slate-800 transition hover:text-brand-600">{{ $video->title }}</a>
                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-500"><span>#{{ $video->id }}</span>@if($video->slug)<span class="pb-1 text-slate-300">•</span><span dir="ltr">{{ $video->slug }}</span>@endif</div>
                        <x-admin.list.row-actions>
                            <x-admin.list.action tag="a" href="{{ route('admin.videos.edit', $video) }}" variant="primary">ویرایش</x-admin.list.action>
                            <span class="text-slate-300">|</span>
                            <x-admin.list.action tag="a" href="{{ $video->url }}" target="_blank">مشاهده</x-admin.list.action>
                            <span class="text-slate-300">|</span>
                            <x-admin.form action="{{ route('admin.videos.destroy', $video) }}" method="delete" class="js-confirm" data-confirm-title="حذف ویدیو" data-confirm-text="آیا از حذف ویدیوی «{{ $video->title }}» مطمئن هستید؟">
                                <x-admin.list.action tag="button" type="submit" variant="danger">حذف</x-admin.list.action>
                            </x-admin.form>
                        </x-admin.list.row-actions>
                    </div>
                </x-admin.list.td>
                <x-admin.list.td>
                    @if($video->author)
                        <x-admin.badge color="slate" size="md" class="whitespace-nowrap [&_svg]:h-[13px] [&_svg]:w-[13px]">@include('icons.user') {{ $video->author->display_name }} ({{ $video->author->id }})</x-admin.badge>
                    @else
                        <span class="text-xs text-slate-400">---</span>
                    @endif
                </x-admin.list.td>
                <x-admin.list.td><x-admin.badge color="slate" size="md">{{ $video->source_type->label() }}</x-admin.badge></x-admin.list.td>
                <x-admin.list.td><x-admin.badge :color="$video->status === \App\Domain\Video\Enums\VideoStatusEnum::ACTIVE ? 'green' : 'red'">{{ $video->status->label() }}</x-admin.badge></x-admin.list.td>
                <x-admin.list.td><div class="flex flex-wrap gap-2">@forelse($video->categories as $category)<x-admin.badge size="md" class="whitespace-nowrap">{{ $category->title }}</x-admin.badge>@empty<span class="text-xs text-slate-400">---</span>@endforelse</div></x-admin.list.td>
                <x-admin.list.td><a href="{{ route('admin.comments.index', ['commentable_type' => 'video']) }}"><x-admin.badge size="md">{{ $video->comments_count }}</x-admin.badge></a></x-admin.list.td>
                <x-admin.list.td><x-admin.badge size="md">{{ page_views($video) }}</x-admin.badge></x-admin.list.td>
                <x-admin.list.td><x-admin.seo.score-indicator :score="$video->seoAnalysis?->score"/></x-admin.list.td>
                <x-admin.list.td muted><div class="text-xs leading-6"><div>{{ jalali_date($video->published_at, 'Y/m/d') }}</div><div>{{ jalali_date($video->published_at, 'H:i') }}</div></div></x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty title="ویدیویی برای نمایش وجود ندارد" description="هنوز ویدیویی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است." action-text="افزودن ویدیوی جدید" :action-href="route('admin.videos.create')"/>
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
