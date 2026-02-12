<?php

use App\Domain\Content\Models\Post;
use Livewire\Component;

new class extends Component {

    public $title = '';
    public $slug = '';
    public ?int $postId = null;
    public bool $manualEdit = false;

    public function mount($initialTitle = '', $initialSlug = '', $postId = null) {
        $this->title = $initialTitle;
        $this->slug = $initialSlug ?: $this->generatePersianSlug($initialTitle);
        $this->postId = $postId;
    }

    public function updatedTitle($value)
    {
        if (!$this->manualEdit) {
            $this->slug = $this->generatePersianSlug($value);
            $this->checkUnique();
        }
    }

    public function updatedSlug($value)
    {
        $this->manualEdit = true;
        $this->slug = $this->cleanPersianSlug($value);
        $this->checkUnique();
    }

    private function checkUnique()
    {
        if (!$this->slug) return;

        $original = $this->slug;
        $count = 1;

        while (
        Post::where('slug', $this->slug)
            ->when($this->postId, fn($q) => $q->where('id', '!=', $this->postId)) // 👈 این خط
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
