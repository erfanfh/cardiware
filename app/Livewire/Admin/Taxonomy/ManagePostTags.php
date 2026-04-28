<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\CreateTaxonomyTermAction;
use App\Application\Taxonomy\Actions\DeleteTaxonomyTermAction;
use App\Application\Taxonomy\Actions\HandleTaxonomyBulkAction;
use App\Application\Taxonomy\Actions\UpdateTaxonomyTermAction;
use App\Application\Taxonomy\Support\TagBulkActions;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Livewire\Admin\Taxonomy\Base\ManageTaxonomyTerms;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ManagePostTags extends ManageTaxonomyTerms
{
    protected function taxonomyKey(): TaxonomyKeyEnum
    {
        return TaxonomyKeyEnum::POST_TAG;
    }

    /**
     * @throws Throwable
     */
    protected function createTerm(): void
    {
        Gate::authorize('tag.manage');

        $term = app(CreateTaxonomyTermAction::class)->execute(
            $this->taxonomyKey(),
            $this->validate()
        );

        $this->highlightedId = $term->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'برچسب نوشته با موفقیت ایجاد شد.');
    }

    public function startEditing(int $id): void
    {
        $term = $this->taxonomyTermsBaseQuery()->findOrFail($id);

        $this->editingId = $term->id;
        $this->title = $term->title;
        $this->slug = $term->slug ?? '';
        $this->description = $term->description ?? '';

        $this->resetValidation();

        $this->dispatch('taxonomy-edit-started');
    }

    /**
     * @throws Throwable
     */
    protected function updateTerm(): void
    {
        Gate::authorize('tag.manage');

        abort_unless($this->editingId, 404);

        $term = $this->taxonomyTermsBaseQuery()->findOrFail($this->editingId);
        $term = app(UpdateTaxonomyTermAction::class)->execute(
            $term,
            $this->taxonomyKey(),
            $this->validate()
        );

        $updatedId = $term->id;

        $this->resetForm();
        $this->highlightedId = $updatedId;

        session()->flash('success', 'برچسب نوشته با موفقیت به‌روزرسانی شد.');
    }

    protected function deleteTerm(): void
    {
        Gate::authorize('tag.manage');

        abort_unless($this->deletingId, 404);

        $term = $this->taxonomyTermsBaseQuery()->findOrFail($this->deletingId);
        $termId = $term->id;

        app(DeleteTaxonomyTermAction::class)->execute($term);

        if ($this->editingId === $termId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$termId]));
        $this->deletingId = null;

        session()->flash('success', 'برچسب نوشته با موفقیت حذف شد.');

        $this->dispatch('taxonomy-list-updating-stop');
    }

    protected function getTermsQuery(): Builder
    {
        return $this->taxonomyTermListing->handle(
            $this->taxonomyTermsBaseQuery()->withCount('posts'),
            $this->listingFilters()
        );
    }

    protected function handleBulkAction(array $selectedIds): mixed
    {
        Gate::authorize('tag.manage');

        return app(HandleTaxonomyBulkAction::class)->execute(
            taxonomy: $this->taxonomyKey(),
            action: $this->bulkAction,
            ids: $selectedIds,
        );
    }

    protected function bulkActionSuccessMessage(mixed $result): string
    {
        return app(HandleTaxonomyBulkAction::class)->successMessage(
            $this->taxonomyKey(),
            $result
        );
    }

    protected function taxonomyTermsBaseQuery(): Builder
    {
        return TaxonomyTerm::query()->forTaxonomy($this->taxonomyKey());
    }

    public function render(): View
    {
        $terms = $this->getTermsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->taxonomyTermListing->perPage());

        return view('livewire.admin.taxonomy.manage-post-tags', [
            'items' => $terms,
            'taxonomyLabel' => $this->taxonomyLabel(),
            'taxonomyLabelPlural' => $this->taxonomyLabelPlural(),
            'bulkActionOptions' => TagBulkActions::set()->toOptions(),
        ]);
    }
}
