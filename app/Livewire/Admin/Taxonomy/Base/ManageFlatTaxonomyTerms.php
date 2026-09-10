<?php

namespace App\Livewire\Admin\Taxonomy\Base;

use App\Application\Taxonomy\Actions\CreateTaxonomyTermAction;
use App\Application\Taxonomy\Actions\DeleteTaxonomyTermAction;
use App\Application\Taxonomy\Actions\HandleTaxonomyBulkAction;
use App\Application\Taxonomy\Actions\UpdateTaxonomyTermAction;
use App\Application\Taxonomy\Support\TagBulkActions;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Throwable;

abstract class ManageFlatTaxonomyTerms extends ManageTaxonomyTerms
{
    abstract protected function taxonomyKey(): TaxonomyKeyEnum;

    abstract protected function permissionName(): string;

    abstract protected function countRelationName(): string;

    abstract protected function viewName(): string;

    abstract protected function successEntityName(): string;

    abstract protected function frontRouteName(): string;

    /**
     * @throws Throwable
     */
    protected function createTerm(): void
    {
        Gate::authorize($this->permissionName());

        $term = app(CreateTaxonomyTermAction::class)->execute(
            $this->taxonomyKey(),
            $this->validate()
        );

        $this->highlightedId = $term->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', $this->successEntityName() . ' با موفقیت ایجاد شد.');
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
        $this->dispatch('admin-inline-edit-started');
    }

    /**
     * @throws Throwable
     */
    protected function updateTerm(): void
    {
        Gate::authorize($this->permissionName());

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

        session()->flash('success', $this->successEntityName() . ' با موفقیت به‌روزرسانی شد.');
    }

    protected function deleteTerm(): void
    {
        Gate::authorize($this->permissionName());

        abort_unless($this->deletingId, 404);

        $term = $this->taxonomyTermsBaseQuery()->findOrFail($this->deletingId);
        $termId = $term->id;

        app(DeleteTaxonomyTermAction::class)->execute($term);

        if ($this->editingId === $termId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$termId]));
        $this->deletingId = null;

        session()->flash('success', $this->successEntityName() . ' با موفقیت حذف شد.');

        $this->dispatch('taxonomy-list-updating-stop');
    }

    protected function getTermsQuery(): Builder
    {
        return $this->taxonomyTermListing->handle(
            $this->taxonomyTermsBaseQuery()->withCount($this->countRelationName()),
            $this->listingFilters()
        );
    }

    protected function handleBulkAction(array $selectedIds): mixed
    {
        Gate::authorize($this->permissionName());

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

    protected function countColumnName(): string
    {
        return $this->countRelationName() . '_count';
    }

    protected function allowedSortColumns(): array
    {
        return [
            'title',
            'slug',
            'created_at',
            $this->countColumnName(),
        ];
    }

    protected function safeSortBy(): string
    {
        return in_array($this->sortBy, $this->allowedSortColumns(), true)
            ? $this->sortBy
            : 'created_at';
    }

    protected function safeSortDirection(): string
    {
        return $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }

    public function render(): View
    {
        $terms = $this->getTermsQuery()
            ->orderBy($this->safeSortBy(), $this->safeSortDirection())
            ->paginate($this->taxonomyTermListing->perPage());

        return view($this->viewName(), [
            'items' => $terms,
            'taxonomyLabel' => $this->taxonomyLabel(),
            'taxonomyLabelPlural' => $this->taxonomyLabelPlural(),
            'bulkActionOptions' => TagBulkActions::set()->toOptions(),
            'countColumn' => $this->countColumnName(),
            'frontRouteName' => $this->frontRouteName(),
        ]);
    }
}
