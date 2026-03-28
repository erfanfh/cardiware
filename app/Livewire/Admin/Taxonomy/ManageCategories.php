<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\CreateCategoryAction;
use App\Application\Taxonomy\Actions\HandleCategoryBulkAction;
use App\Application\Taxonomy\Actions\UpdateCategoryAction;
use App\Application\Taxonomy\Support\CategoryBulkActions;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Livewire\Admin\Taxonomy\Base\ManageTaxonomyTerms;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class ManageCategories extends ManageTaxonomyTerms
{
    /**
     * @throws Throwable
     */
    protected function createTerm(): void
    {
        $term = app(CreateCategoryAction::class)->execute($this->validate());

        $this->highlightedId = $term->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function startEditing(int $id): void
    {
        $term = TaxonomyTerm::categories()->findOrFail($id);

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

        $term = TaxonomyTerm::categories()->findOrFail($this->editingId);
        $term = app(UpdateCategoryAction::class)->execute($term, $this->validate());

        $updatedId = $term->id;

        $this->resetForm();
        $this->highlightedId = $updatedId;

        session()->flash('success', 'دسته‌بندی با موفقیت به‌روزرسانی شد.');
    }

    public function deleteTerm(): void
    {
        abort_unless($this->deletingId, 404);

        $term = TaxonomyTerm::categories()->findOrFail($this->deletingId);
        $termId = $term->id;

        $term->delete();

        if ($this->editingId === $termId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$termId]));
        $this->deletingId = null;

        session()->flash('success', 'دسته‌بندی با موفقیت حذف شد.');

        $this->dispatch('taxonomy-list-updating-stop');
    }

    protected function getTermsQuery(): Builder
    {
        return $this->taxonomyTermListing->handle(
            TaxonomyTerm::categories(),
            $this->listingFilters()
        );
    }

    protected function handleBulkAction(array $selectedIds): mixed
    {
        return app(HandleCategoryBulkAction::class)->execute(
            action: $this->bulkAction,
            ids: $selectedIds,
        );
    }

    protected function bulkActionSuccessMessage(mixed $result): string
    {
        return app(HandleCategoryBulkAction::class)->successMessage($result);
    }

    protected function taxonomyLabel(): string
    {
        return 'دسته‌بندی';
    }

    protected function taxonomyLabelPlural(): string
    {
        return 'دسته‌بندی‌ها';
    }

    protected function taxonomyTypeValue(): string|int
    {
        return TaxonomyTermTypeEnum::CATEGORY->value;
    }

    public function render(): View
    {
        $terms = $this->getTermsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->taxonomyTermListing->perPage());

        return view('livewire.admin.taxonomy.manage-categories', [
            'items' => $terms,
            'taxonomyLabel' => $this->taxonomyLabel(),
            'taxonomyLabelPlural' => $this->taxonomyLabelPlural(),
            'bulkActionOptions' => CategoryBulkActions::set()->toOptions(),
        ]);
    }
}
