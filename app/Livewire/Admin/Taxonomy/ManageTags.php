<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\CreateTagAction;
use App\Application\Taxonomy\Actions\HandleTagBulkAction;
use App\Application\Taxonomy\Actions\UpdateTagAction;
use App\Application\Taxonomy\Support\TagBulkActions;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Livewire\Admin\Taxonomy\Base\ManageTaxonomyTerms;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class ManageTags extends ManageTaxonomyTerms
{
    /**
     * @throws Throwable
     */
    public function createTerm(): void
    {
        $term = app(CreateTagAction::class)->execute($this->validate());

        $this->highlightedId = $term->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'برچسب با موفقیت ایجاد شد.');
    }

    public function startEditing(int $id): void
    {
        $term = TaxonomyTerm::tags()->findOrFail($id);

        $this->editingId = $term->id;
        $this->title = $term->title;
        $this->slug = $term->slug ?? '';
        $this->description = $term->description ?? '';

        $this->resetValidation();

        $this->dispatch('taxonomy-edit-started');
    }

    public function updateTerm(): void
    {
        abort_unless($this->editingId, 404);

        $term = TaxonomyTerm::tags()->findOrFail($this->editingId);
        $term = app(UpdateTagAction::class)->execute($term, $this->validate());

        $updatedId = $term->id;

        $this->resetForm();
        $this->highlightedId = $updatedId;

        session()->flash('success', 'برچسب با موفقیت به‌روزرسانی شد.');
    }

    public function deleteTerm(): void
    {
        abort_unless($this->deletingId, 404);

        $term = TaxonomyTerm::tags()->findOrFail($this->deletingId);
        $termId = $term->id;

        $term->delete();

        if ($this->editingId === $termId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$termId]));
        $this->deletingId = null;

        session()->flash('success', 'برچسب با موفقیت حذف شد.');

        $this->dispatch('taxonomy-list-updating-stop');
    }

    protected function getTermsQuery(): Builder
    {
        return $this->taxonomyTermListing->handle(
            TaxonomyTerm::tags(),
            $this->listingFilters()
        );
    }

    protected function handleBulkAction(array $selectedIds): mixed
    {
        return app(HandleTagBulkAction::class)->execute(
            action: $this->bulkAction,
            ids: $selectedIds,
        );
    }

    protected function bulkActionSuccessMessage(mixed $result): string
    {
        return app(HandleTagBulkAction::class)->successMessage($result);
    }

    protected function taxonomyLabel(): string
    {
        return 'برچسب';
    }

    protected function taxonomyLabelPlural(): string
    {
        return 'برچسب‌ها';
    }

    protected function taxonomyTypeValue(): string|int
    {
        return TaxonomyTermTypeEnum::TAG->value;
    }

    public function render(): View
    {
        $terms = $this->getTermsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->taxonomyTermListing->perPage());

        return view('livewire.admin.taxonomy.manage-tags', [
            'items' => $terms,
            'taxonomyLabel' => $this->taxonomyLabel(),
            'taxonomyLabelPlural' => $this->taxonomyLabelPlural(),
            'bulkActionOptions' => TagBulkActions::set()->toOptions(),
        ]);
    }
}
