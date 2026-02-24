<?php

use App\Domain\Content\Models\Post;
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
    public function posts(): LengthAwarePaginator
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

        return Post::query()->withTrashed()->whereNull('deleted_at')
            ->when($request->filled('title'), function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->title . '%');
            })->when($request->filled('id'), function ($query) use ($request) {
                $query->where('id', $request->id);
            })->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('id', $request->category);
                });
            })->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('id', $request->tag);
                });
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
    #[On('post-updated')]
    public function postUpdated(): void
    {
        $this->posts();
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
    <div class="-mx-0.5">
        <table class="w-full text-sm border-collapse table-auto">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">تصویر
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">
                    <a href="{{ sortLink('title') }}" class="flex items-center gap-1">
                        نوشته {!! sortIcon('title') !!}
                        @if(request('sort') !== 'title')
                            <div class="flex">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="1.5"
                                          d="m10.397 18.586l-.76-.967c-1.234-1.565-1.851-2.348-1.57-2.984C8.348 14 9.312 14 11.24 14h1.522c1.927 0 2.89 0 3.172.635c.281.636-.336 1.419-1.57 2.984l-.76.967C12.86 19.529 12.488 20 12 20s-.86-.471-1.603-1.414M12 14V4"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="1.5"
                                          d="M12 10v10m1.603-14.586l.76.967c1.234 1.565 1.851 2.348 1.57 2.984c-.281.635-1.245.635-3.172.635H11.24c-1.927 0-2.89 0-3.172-.635c-.281-.636.336-1.419 1.57-2.984l.76-.967C11.14 4.471 11.511 4 12 4s.86.471 1.603 1.414"/>
                                </svg>
                            </div>
                        @endif
                    </a>
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">شناسه
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">
                    نویسنده
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">دسته
                    ها
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">برچسب
                    ها
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">وضعیت
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">
                    <a href="{{ sortLink('published_at') }}" class="flex items-center gap-1">
                        تاریخ انتشار {!! sortIcon('published_at') !!}
                        @if(request('sort') !== 'published_at')
                            <div class="flex">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="1.5"
                                          d="m10.397 18.586l-.76-.967c-1.234-1.565-1.851-2.348-1.57-2.984C8.348 14 9.312 14 11.24 14h1.522c1.927 0 2.89 0 3.172.635c.281.636-.336 1.419-1.57 2.984l-.76.967C12.86 19.529 12.488 20 12 20s-.86-.471-1.603-1.414M12 14V4"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="1.5"
                                          d="M12 10v10m1.603-14.586l.76.967c1.234 1.565 1.851 2.348 1.57 2.984c-.281.635-1.245.635-3.172.635H11.24c-1.927 0-2.89 0-3.172-.635c-.281-.636.336-1.419 1.57-2.984l.76-.967C11.14 4.471 11.511 4 12 4s.86.471 1.603 1.414"/>
                                </svg>
                            </div>
                        @endif
                    </a>
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">تعداد
                    بازدید
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">تعداد
                    کامنت ها
                </th>
                <th class="text-right p-3 font-semibold text-gray-500 dark:text-gray-400 text-xs">عملیات</th>
            </tr>
            </thead>

            <tbody>
            @forelse($this->posts as $post)
                <tr wire:key="{{ $post->id }}"
                    class="even:bg-gray-50/50 dark:even:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                    <td></td>

                    <td class="p-3 break-words whitespace-normal">
                        <a href="#" target="_blank"
                           class="flex items-start gap-3 group">
                            <div>
                                <div
                                    class="font-semibold text-gray-800 dark:text-gray-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                    {{ $post->title }}
                                </div>
                            </div>
                        </a>
                    </td>

                    <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal">{{ $post->id }}</td>

                    <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal">{{ $post->author->first_name . ' ' . $post->author->last_name }}</td>

                    <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal max-w-xs">{{ $post->categories->pluck('title')->implode(', ') }}</td>

                    <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal max-w-xs">{{ $post->tags->pluck('title')->implode(', ') }}</td>

                    <td class="p-3 break-words whitespace-normal">
                        @if($post->status == \App\Domain\Content\Enums\StatusEnum::DRAFT)
                            <span
                                class=" whitespace-nowrap inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::DRAFT->label() }}
                                </span>
                        @elseif($post->status == \App\Domain\Content\Enums\StatusEnum::PENDING_REVIEW)
                            <span
                                class=" whitespace-nowrap inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::PENDING_REVIEW->label() }}
                                </span>
                        @elseif($post->status == \App\Domain\Content\Enums\StatusEnum::PUBLISHED)
                            <span
                                class=" whitespace-nowrap inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    {{ \App\Domain\Content\Enums\StatusEnum::PUBLISHED->label() }}
                                </span>
                        @endif
                    </td>

                    <td class="p-3 text-gray-600 dark:text-gray-300 break-words whitespace-normal">
                        {{ jalali_date($post->published_at, 'Y/m/d H:i') }}
                    </td>

                    <td></td>

                    <td></td>

                    <td class="p-3 break-words whitespace-normal">
                        <div class="flex flex-wrap gap-1.5 justify-start">
                            <livewire:admin.content.post.quick-edit-button :post-id="$post->id"/>

                            <a href="{{ route('admin.posts.edit', $post) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <svg width="14" height="14" viewBox="0 0 24 24">
                                    <path d="M5 7h14M5 12h14M5 17h14"
                                          stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                                ویرایش
                            </a>

                            <form method="POST"
                                  action="{{ route('admin.posts.destroy', $post) }}"
                                  class="inline-block"
                                  data-confirm-title="حذف نوشته"
                                  data-confirm-text="نوشته برای همیشه حذف خواهد شد."
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
                        <h3 class="text-gray-500 dark:text-gray-400 font-medium">هیچ نوشته ای یافت نشد!</h3>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
        {{ $this->posts->links() }}
    </div>
</div>
