<?php

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Livewire\Component;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

new class extends Component {
    public $title = '';
    public $parentCategory = null;

    protected $rules = [
        'title' => 'required|min:2|max:255',
        'parentCategory' => 'nullable|exists:taxonomy_terms,id'
    ];

    public function create()
    {
        $this->validate();

        $category = TaxonomyTerm::create([
            'title' => $this->title,
            'parent_id' => $this->parentCategory,
            'type' => TaxonomyTermTypeEnum::CATEGORY->value
        ]);

        $this->title = '';
        $this->parentCategory = null;

        $this->dispatch('category-created', [
            'id' => $category->id,
            'title' => $category->title,
        ]);
    }
}

?>

<div class="space-y-2" wire:ignore>
    <input type="text"
           wire:model.defer="title"
           placeholder="دسته جدید..."
           class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">

    <select id="parent-category-select"
            wire:model.defer="parentCategory"
            class="w-full rounded-[10px] border border-solid border-[var(--border-subtle)] py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] bg-white outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400">
    </select>

    <button type="button"
            wire:click="create"
            class="bg-[var(--brand-500)] text-white text-[0.86rem] inline-flex items-center justify-center rounded-full py-[7px] px-[14px] cursor-pointer hover:bg-[var(--brand-600)] hover:transform">
        افزودن
    </button>
</div>
