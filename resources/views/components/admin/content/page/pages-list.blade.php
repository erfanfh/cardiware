<?php

use App\Domain\Content\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    /**
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function pages(): LengthAwarePaginator
    {
        $request = request();

        $sort = $request->get('sort', 'published_at');
        $direction = $request->get('direction', 'desc');

        $allowedSorts = ['title', 'published_at'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'published_at';
        }

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        return page::query()->withTrashed()->whereNull('deleted_at')
            ->when($request->filled('title'), function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->title . '%');
            })->when($request->filled('id'), function ($query) use ($request) {
                $query->where('id', $request->id);
            })->when($request->filled('author'), function ($query) use ($request) {
                $query->whereHas('author', function ($q) use ($request) {
                    $q->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ['%' . $request->author . '%']);
                });
            })->when($request->filled('from_published_at'), function ($query) use ($request) {
                $query->where('created_at', '>=', gregorian_date($request->from_published_at));
            })->when($request->filled('to_published_at'), function ($query) use ($request) {
                $query->where('created_at', '<=', gregorian_date($request->to_published_at));
            })->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * @return void
     */
    #[On('page-updated')]
    public function pageUpdated(): void
    {
        $this->pages();
    }
};
?>

@php
    function sortLink($column): string
    {
        $currentSort = request('sort');
        $currentDirection = request('direction', 'asc');

        $direction = ($currentSort === $column && $currentDirection === 'asc')
            ? 'desc'
            : 'asc';

        return route(
            request()->route()->getName(),
            array_merge(request()->query(), [
                'sort' => $column,
                'direction' => $direction,
            ])
        );
    }

    function sortIcon($column): string
    {
        if (request('sort') !== $column) return '';

        return request('direction') === 'asc'
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><!-- Icon from Huge Icons by Hugeicons - undefined --><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v10m1.603-14.586l.76.967c1.234 1.565 1.851 2.348 1.57 2.984c-.281.635-1.245.635-3.172.635H11.24c-1.927 0-2.89 0-3.172-.635c-.281-.636.336-1.419 1.57-2.984l.76-.967C11.14 4.471 11.511 4 12 4s.86.471 1.603 1.414"/></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><!-- Icon from Huge Icons by Hugeicons - undefined --><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m10.397 18.586l-.76-.967c-1.234-1.565-1.851-2.348-1.57-2.984C8.348 14 9.312 14 11.24 14h1.522c1.927 0 2.89 0 3.172.635c.281.636-.336 1.419-1.57 2.984l-.76.967C12.86 19.529 12.488 20 12 20s-.86-.471-1.603-1.414M12 14V4"/></svg>';
    }
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto -mx-0.5">
        <table class="w-full text-sm border-collapse">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[200px]">تصویر
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[200px]">
                    <a href="{{ sortLink('title') }}" class="flex items-center gap-1">
                        صفحه {!! sortIcon('title') !!}
                    </a>
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[50px]">شناسه
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[110px]">
                    نویسنده
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[100px]">وضعیت
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs min-w-[135px]">
                    <a href="{{ sortLink('published_at') }}" class="flex items-center gap-1">
                        تاریخ انتشار {!! sortIcon('published_at') !!}
                    </a>
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">عملیات</th>
            </tr>
            </thead>

            <tbody>
            @forelse($this->pages as $page)
                <tr wire:key="{{ $page->id }}"
                    class="even:bg-gray-50/50 dark:even:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                    <td></td>

                    <td class="p-3">
                        <a href="#" target="_blank"
                           class="flex items-center gap-3 group">
                            <div
                                class="w-8 h-8 min-w-8 rounded-lg bg-gradient-to-r from-orange-500 to-orange-500 text-white flex items-center justify-center text-sm font-semibold overflow-hidden">
                                @if($page->picture?->url)
                                    <img src="{{ $page->picture->url }}" alt="{{ $page->title }}"
                                         class="w-full h-full object-cover">
                                @else
                                    {{ mb_substr($page->title ?? 'صفحه', 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <div
                                    class="font-semibold text-gray-800 dark:text-gray-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                    {{ $page->title }}
                                </div>
                            </div>
                        </a>
                    </td>

                    <td class="p-3 text-gray-600 dark:text-gray-300">{{ $page->id }}</td>

                    <td class="p-3 text-gray-600 dark:text-gray-300">{{ $page->author->first_name . ' ' . $page->author->last_name }}</td>

                    <td class="p-3">
                        @if($page->status == \App\Domain\Content\Enums\StatusEnum::DRAFT)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::DRAFT->value }}
                                </span>
                        @elseif($page->status == \App\Domain\Content\Enums\StatusEnum::PENDING_REVIEW)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::PENDING_REVIEW->value }}
                                </span>
                        @elseif($page->status == \App\Domain\Content\Enums\StatusEnum::PUBLISHED)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::PUBLISHED->value }}
                                </span>
                        @endif
                    </td>

                    <td class="p-3 text-gray-600 dark:text-gray-300">
                        {{ jalali_date($page->published_at, 'Y/m/d H:i') }}
                    </td>

                    <td class="p-3">
                        <div class="flex gap-1.5 justify-start">
                            <livewire:admin.content.page.quick-edit-button :page-id="$page->id"/>

                            <a href="{{ route('admin.pages.edit', $page) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <svg width="14" height="14" viewBox="0 0 24 24">
                                    <path d="M5 7h14M5 12h14M5 17h14"
                                          stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                                ویرایش
                            </a>

                            <form method="post"
                                  action="{{ route('admin.pages.destroy', $page) }}"
                                  class="inline-block js-confirm"
                                  data-confirm-title="حذف صفحه"
                                  data-confirm-text="صفحه برای همیشه حذف خواهد شد."
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-medium hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                         viewBox="0 0 24 24">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round"
                                              stroke-linejoin="round" stroke-width="1.5" d="M18 6L6 18m12 0L6 6"/>
                                    </svg>
                                    حذف
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-4 text-center">
                        <h3 class="text-gray-500 dark:text-gray-400 font-medium">هیچ صفحه ای یافت نشد!</h3>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
        {{ $this->pages->links() }}
    </div>
</div>
