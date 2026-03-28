<?php

use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Page;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public Page $page;
    public bool $show = false;
    public string $title;
    public string $slug;
    public string $published_at;
    public string $body;
    public string $excerpt;
    public PostStatusEnum $status;
    public bool $manualEdit = false;

    /**
     * @param int $id
     * @return void
     */
    #[On('quick-edit-page')]
    public function showForm(int $id): void
    {
        $this->page = Page::findOrFail($id);
        $this->title = $this->page->title;
        $this->slug = $this->page->slug;
        $this->published_at = jalali_date($this->page->published_at);
        $this->status = $this->page->status;
        $this->show = true;
    }

    /**
     * @return void
     */
    public function hideForm(): void
    {
        $this->show = false;
    }

    /**
     * @return void
     */
    public function updatePage(): void
    {
        $slugId = $this->page->slugs()->where('is_current', true)->first()->id;

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'slug' => [
                'required',
                'max:255',
                'alpha_dash',
                Rule::unique('content_slugs', 'slug')->ignore($slugId),
            ],
        ]);
        $this->page->update([
            'title' => $this->title,
            'published_at' => $this->published_at ? gregorian_date($this->published_at) : now(),
            'author_id' => 1,
            'slug' => $this->slug
        ]);

        $this->dispatch('page-updated');
        $this->reset();
    }

    /**
     * @param string $value
     * @return void
     */
    public function updatedTitle(string $value): void
    {
        if (!$this->manualEdit) {
            $this->slug = $this->generatePersianSlug($value);
            $this->checkUnique();
        }
    }

    /**
     * @param string $value
     * @return void
     */
    public function updatedSlug(string $value): void
    {
        $this->manualEdit = true;
        $this->slug = $this->cleanPersianSlug($value);
        $this->checkUnique();
    }

    /**
     * @return void
     */
    private function checkUnique(): void
    {
        if (!$this->slug)
            return;

        $original = $this->slug;
        $count = 1;

        while (
        ContentSlug::where('sluggable_type', page::class)
            ->where('slug', $this->slug)
            ->when($this->page->id, fn($q) => $q->where('id', '!=', $this->page->id))
            ->exists()
        ) {
            $this->slug = $original . '-' . $count;
            $count++;
        }
    }

    /**
     * @param string $title
     * @return string
     */
    private function generatePersianSlug(string $title): string
    {
        $slug = str_replace(' ', '-', $title);
        return $this->cleanPersianSlug($slug);
    }

    /**
     * @param string $slug
     * @return string
     */
    private function cleanPersianSlug(string $slug): string
    {
        $slug = preg_replace('/[^\p{Arabic}a-zA-Z0-9\-]/u', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
};
?>

<div>
    @if($show)
        <div
            class="space-y-6 bg-white dark:bg-gray-800 rounded-[var(--radius-xl)] shadow-[var(--shadow-soft)] border border-gray-200 dark:border-gray-700 p-5 mb-12">
            <form wire:submit="updatePage">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">ویرایش سریع</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <div>
                            <label for="title" class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                <span class="text-red-500">*</span>
                                عنوان صفحه
                            </label>
                            <input
                                type="text"
                                id="title"
                                wire:model.live="title"
                                name="title"
                                class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                            >

                            <div class="mt-4">
                                <label for="slug"
                                       class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">
                                    لینک یکتا
                                </label>
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-600" dir="ltr">
                                        <span>{{ url('/') }}/</span>
                                        <input
                                            type="text"
                                            id="slug"
                                            name="slug"
                                            wire:model.blur="slug"
                                            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                                            dir="ltr"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="published_at" class="text-xs font-medium text-gray-500 dark:text-gray-400">تاریخ
                            انتشار</label>
                        <input
                            data-jdp
                            autocomplete="off"
                            id="published_at"
                            wire:model="published_at"
                            value="{{ $page->published_at ? jalali_date($page->published_at) : '' }}"
                            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                        >
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="status" class="text-xs font-medium text-gray-500 dark:text-gray-400">وضعیت</label>
                        <select wire:model="status" autocomplete="off" id="status"
                                class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                            @foreach (PostStatusEnum::cases() as $status)
                                <option @if($page and $page->status === $status->value) selected
                                        @endif value="{{ $status->value }}">
                                    {{ $status->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-2 my-3">
                    <button type="button" wire:click="hideForm"
                            class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 rounded-full shadow-sm border border-gray-300 dark:border-gray-700 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 cursor-pointer">
                        انصراف
                    </button>

                    <button type="submit"
                            class="bg-[var(--brand-500)] text-white text-[0.86rem] inline-flex items-center justify-center rounded-full py-[7px] px-[14px] cursor-pointer hover:bg-[var(--brand-600)] hover:transform">
                        <span>ذخیره</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
