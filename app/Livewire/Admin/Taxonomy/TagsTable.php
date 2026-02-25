<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\BulkDeleteAction;
use App\Application\Taxonomy\Actions\GeneratePathAction;
use App\Application\Taxonomy\Actions\RecordSlugHistoryAction;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Infrastructure\Traits\Searchable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TagsTable extends Component
{
    use WithPagination, Searchable;

    public $search = '';
    public $editingId = null;
    public $title = '';
    public $slug = '';
    public $description = '';
    public $bulkAction = '';
    public $selectedIds = [];
    public $slugChangeConfirmed = false;
    public $originalSlug = '';

    protected $listeners = ['tagCreated' => '$refresh', 'tagUpdated' => '$refresh'];

    public function mount($editingId = null, $search = '')
    {
        $this->editingId = $editingId;
        $this->search = $search;

        if ($editingId) {
            $term = TaxonomyTerm::findOrFail($editingId);
            $this->title = $term->title;
            $this->slug = $term->slug;
            $this->description = $term->description;
            $this->originalSlug = $term->slug;
        }
    }

    #[Computed]
    public function tags()
    {
        $query = TaxonomyTerm::tag()->orderBy('title');

        if ($this->search) {
            $query = $this->applySearch($query, $this->search, ['title', 'slug', 'description']);
        }

        return $query->get();
    }

    #[Computed]
    public function editingTerm()
    {
        return $this->editingId ? TaxonomyTerm::find($this->editingId) : null;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = [
            'type' => TaxonomyTermTypeEnum::TAG->value,
            'title' => $this->title,
            'slug' => empty(trim($this->slug)) ? null : $this->slug,
            'description' => $this->description,
        ];

        if ($this->editingId) {
            // Update
            $tag = TaxonomyTerm::findOrFail($this->editingId);
            $oldSlug = $tag->slug;

            // Check slug change confirmation
            if ($this->slug !== $oldSlug && !$this->slugChangeConfirmed) {
                $this->dispatch('showSlugWarning',
                    oldSlug: $oldSlug,
                    newSlug: $this->slug
                );
                return;
            }

            $tag->update($data);

            if ($oldSlug !== $tag->slug) {
                app(RecordSlugHistoryAction::class)->execute($tag, $oldSlug);
            }

            $this->dispatch('tagUpdated');
            $this->resetForm();
            session()->flash('success', 'برچسب با موفقیت بروزرسانی شد.');
        } else {
            // Create
            $originalSlug = $this->slug;
            $tag = TaxonomyTerm::create($data);

            if ($originalSlug && $originalSlug !== $tag->slug) {
                session()->flash('warning', "اسلاگ '{$originalSlug}' از قبل وجود داشت. اسلاگ '{$tag->slug}' تنظیم شد.");
            }

            // Generate path
            $tag->path = app(GeneratePathAction::class)->execute($tag);
            $tag->saveQuietly();

            $this->dispatch('tagCreated');
            $this->resetForm();
            session()->flash('success', 'برچسب ایجاد شد.');
        }
    }

    public function edit($id)
    {
        $this->editingId = $id;
        $term = TaxonomyTerm::findOrFail($id);
        $this->title = $term->title;
        $this->slug = $term->slug;
        $this->description = $term->description;
        $this->originalSlug = $term->slug;
        $this->slugChangeConfirmed = false;
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->editingId = null;
    }

    public function delete($id)
    {
        $tag = TaxonomyTerm::findOrFail($id);
        $tag->delete();
        session()->flash('success', 'برچسب حذف شد.');
        $this->dispatch('tagUpdated');
    }

    public function bulkDelete()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'هیچ آیتمی انتخاب نشده است.');
            return;
        }

        $count = app(BulkDeleteAction::class)->execute($this->selectedIds);
        $this->selectedIds = [];
        $this->bulkAction = '';
        session()->flash('success', "{$count} برچسب حذف شد.");
        $this->dispatch('tagUpdated');
    }

    public function toggleSelectAll($value)
    {
        $this->selectedIds = $value ? $this->tags->pluck('id')->toArray() : [];
    }

    public function toggleSelect($id)
    {
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_filter($this->selectedIds, fn($i) => $i !== $id);
        } else {
            $this->selectedIds[] = $id;
        }
    }

    #[On('confirmSlugChange')]
    public function proceedWithSlugChange()
    {
        $this->slugChangeConfirmed = true;
        $this->save();
    }

    #[On('confirmBulkDelete')]
    public function proceedWithBulkDelete()
    {
        $this->bulkDelete();
    }

    private function resetForm()
    {
        $this->reset('title', 'slug', 'description', 'slugChangeConfirmed', 'originalSlug');
    }

    public function render()
    {
        return view('livewire.admin.taxonomy.tags-table');
    }
}
