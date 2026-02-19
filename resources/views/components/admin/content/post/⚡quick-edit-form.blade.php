<?php

use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public Post $post;
    public bool $show = false;
    public $title;
    public $slug;
    public $published_at;
    public $categories;
    public $body;
    public $excerpt;
    public $status;
    public bool $manualEdit = false;

    #[On('quick-edit-post')]
    public function showForm(int $id): void
    {
        $this->post = Post::findOrFail($id);
        $this->title = $this->post->title;
        $this->slug = $this->post->slug;
        $this->published_at = jalali_date($this->post->published_at);
        $this->categories = $this->post->categories->pluck('id');
        $this->body = $this->post->body;
        $this->excerpt = $this->post->excerpt;
        $this->status = $this->post->status;
        $this->show = true;

        $this->dispatch('init-category-select');
    }

    public function hideForm()
    {
        $this->show = false;
    }

    public function updatePost()
    {
        $slugId = $this->post->slugs()->where('is_current', true)->first()->id;

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'slug' => [
                'required',
                'max:255',
                'alpha_dash',
                Rule::unique('content_slugs', 'slug')->ignore($slugId),
            ],
        ]);
        $this->post->update([
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'published_at' => $this->published_at ? gregorian_date($this->published_at) : now(),
            'author_id' => 1,
            'slug' => $this->slug
        ]);

        $this->post->categories()->sync(
            collect($this->categories)->mapWithKeys(fn($id) => [
                $id => ['type' => TaxonomyTermTypeEnum::CATEGORY->value]
            ])
        );

        $this->dispatch('post-updated');
        $this->reset();
    }

    public function updatedTitle($value): void
    {
        if (!$this->manualEdit) {
            $this->slug = $this->generatePersianSlug($value);
            $this->checkUnique();
        }
    }

    public function updatedSlug($value): void
    {
        $this->manualEdit = true;
        $this->slug = $this->cleanPersianSlug($value);
        $this->checkUnique();
    }

    private function checkUnique(): void
    {
        if (!$this->slug)
            return;

        $original = $this->slug;
        $count = 1;

        while (
        ContentSlug::where('sluggable_type', Post::class)
            ->where('slug', $this->slug)
            ->when($this->post->id, fn($q) => $q->where('id', '!=', $this->post->id))
            ->exists()
        ) {
            $this->slug = $original . '-' . $count;
            $count++;
        }
    }

    private function generatePersianSlug($title): string
    {
        $slug = str_replace(' ', '-', $title);
        return $this->cleanPersianSlug($slug);
    }

    private function cleanPersianSlug($slug): string
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
            <form wire:submit="updatePost">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">ویرایش سریع</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <div>
                            <label for="title" class="text-[0.85rem] text-[var(--text-main)]">
                                <span class="text-red-500">*</span>
                                عنوان نوشته
                            </label>
                            <input
                                type="text"
                                id="title"
                                wire:model.live="title"
                                name="title"
                                class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                            >

                            <div class="mt-4">
                                <label for="slug" class="text-[0.85rem] text-[var(--text-main)] block mb-1">
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
                                            class="flex-1 rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                                            dir="ltr"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="published_at" class="text-[0.85rem] text-[var(--text-main)]">تاریخ انتشار</label>
                        <input
                            data-jdp
                            autocomplete="off"
                            id="published_at"
                            wire:model="published_at"
                            value="{{ $post->published_at ? jalali_date($post->published_at) : '' }}"
                            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                        >
                    </div>

                    <div class="flex flex-col gap-1" wire:ignore>
                        <label for="categories" class="text-[0.85rem] text-[var(--text-main)]">دسته بندی</label>
                        <select wire:model="categories"
                                placeholder="جستجو کنید..."
                                id="category-select"
                                multiple
                                autocomplete="off"
                                class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                            @foreach($post ? $post->categories : [] as $category)
                                <option selected value="{{ $category->id }}">{{ $category->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 flex flex-col gap-1">
                        <label for="body" class="text-[0.85rem] text-[var(--text-main)]">
                            <span class="text-red-500">*</span>
                            متن نوشته
                        </label>
                        <textarea
                            id="body"
                            wire:model="body"
                            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                            rows="6"
                        >{{ $post->body }}</textarea>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="excerpt" class="text-[0.85rem] text-[var(--text-main)]">
                            <span class="text-red-500">*</span>
                            خلاصه نوشته
                        </label>
                        <input
                            type="text"
                            id="excerpt"
                            wire:model="excerpt"
                            value="{{ $post->excerpt }}"
                            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400"
                        >
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="status" class="text-[0.85rem] text-[var(--text-main)]">وضعیت</label>
                        <select wire:model="status" autocomplete="off" id="status"
                                class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
                            @foreach (\App\Domain\Content\Enums\StatusEnum::cases() as $status)
                                <option @if($post and $post->status === $status->value) selected
                                        @endif value="{{ $status->value }}">
                                    {{ $status->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-2 my-3">
                    <button type="button" wire:click="hideForm"
                            class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow border border-gray-200 dark:border-gray-700 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
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
