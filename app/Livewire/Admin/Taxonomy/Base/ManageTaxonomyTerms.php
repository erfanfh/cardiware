<?php

namespace App\Livewire\Admin\Taxonomy\Base;

use App\Application\Taxonomy\Listings\TaxonomyTermListing;
use App\Domain\Taxonomy\Enums\TaxonomyAdminTemplateEnum;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

abstract class ManageTaxonomyTerms extends Component
{
    use WithPagination;

    protected TaxonomyTermListing $taxonomyTermListing;

    public string $search = '';

    public string $sortBy;

    public string $sortDirection;

    public array $selectedIds = [];

    public string $bulkAction = '';

    public ?int $editingId = null;

    public string $title = '';

    public string $slug = '';

    public string $description = '';

    public ?int $deletingId = null;

    public ?int $highlightedId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'performDeleteTerm' => 'performDeleteTerm',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function boot(TaxonomyTermListing $taxonomyTermListing): void
    {
        $this->taxonomyTermListing = $taxonomyTermListing;
    }

    public function mount(): void
    {
        $default = $this->taxonomyTermListing->defaultSort();

        $this->sortBy = $default['sort'];
        $this->sortDirection = $default['direction'];
    }

    public function getIsEditingProperty(): bool
    {
        return filled($this->editingId);
    }

    public function getHasSelectionsProperty(): bool
    {
        return !empty($this->selectedIds);
    }

    public function getSelectedCountProperty(): int
    {
        return count($this->selectedIds);
    }

    public function getAllVisibleSelectedProperty(): bool
    {
        $visibleIds = $this->getVisibleTermIds();

        if (empty($visibleIds)) {
            return false;
        }

        return count(array_intersect($visibleIds, $this->selectedIds)) === count($visibleIds);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();

        $this->dispatch('taxonomy-scroll-to-list');
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function save(): void
    {
        if ($this->isEditing) {
            $this->updateTerm();

            return;
        }

        $this->createTerm();
    }

    public function cancelEditing(): void
    {
        $this->resetForm();
        $this->dispatch('taxonomy-scroll-to-list');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function toggleSelection(int $id): void
    {
        if (in_array($id, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));

            return;
        }

        $this->selectedIds[] = $id;
        $this->selectedIds = array_values(array_unique($this->selectedIds));
    }

    public function togglePageSelection(): void
    {
        $visibleIds = $this->getVisibleTermIds();

        if (empty($visibleIds)) {
            return;
        }

        if ($this->allVisibleSelected) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, $visibleIds));

            return;
        }

        $this->selectedIds = array_values(array_unique([
            ...$this->selectedIds,
            ...$visibleIds,
        ]));
    }

    public function clearSelection(): void
    {
        $this->selectedIds = [];
    }

    public function applyBulkAction(): void
    {
        if ($this->bulkAction === '' || empty($this->selectedIds)) {
            return;
        }

        $selectedIds = collect($this->selectedIds)
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $selectedBulkAction = $this->bulkAction;
        $result = $this->handleBulkAction($selectedIds);

        if (
            $this->editingId !== null &&
            in_array($this->editingId, $selectedIds, true) &&
            $selectedBulkAction === 'delete'
        ) {
            $this->resetForm();
        }

        $this->clearSelection();
        $this->bulkAction = '';

        session()->flash('success', $this->bulkActionSuccessMessage($result));
    }

    public function sortByColumn(string $column, string $direction = 'asc'): void
    {
        if (!array_key_exists($column, $this->taxonomyTermListing->sortable())) {
            return;
        }

        if ($this->sortBy === $column) {
            if ($this->sortDirection === $direction) {
                $default = $this->taxonomyTermListing->defaultSort();

                $this->sortBy = $default['sort'];
                $this->sortDirection = $default['direction'];
            } else {
                $this->sortDirection = $direction;
            }
        } else {
            $this->sortBy = $column;
            $this->sortDirection = $direction;
        }

        $this->resetPage();
        $this->clearSelection();

        $this->dispatch('taxonomy-scroll-to-list');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->slug = '';
        $this->description = '';

        $this->resetValidation();
        $this->resetExtraFormFields();
    }

    protected function resetExtraFormFields(): void
    {
    }

    public function nextPage($pageName = 'page'): void
    {
        $this->setPage(($this->getPage($pageName) ?? 1) + 1, $pageName);
        $this->dispatch('taxonomy-scroll-to-list');
    }

    public function previousPage($pageName = 'page'): void
    {
        $currentPage = (int)($this->getPage($pageName) ?? 1);

        if ($currentPage > 1) {
            $this->setPage($currentPage - 1, $pageName);
        }

        $this->dispatch('taxonomy-scroll-to-list');
    }

    public function gotoPage($page, $pageName = 'page'): void
    {
        $this->setPage($page, $pageName);
        $this->dispatch('taxonomy-scroll-to-list');
    }

    public function performDeleteTerm($id = null): void
    {
        if (is_array($id)) {
            $id = $id['id'] ?? null;
        }

        $id = (int)$id;

        if (!$id) {
            return;
        }

        $this->deletingId = $id;
        $this->deleteTerm();
    }

    /**
     * @return array<string, mixed>
     */
    protected function listingFilters(): array
    {
        return [
            'search' => $this->search,
            'sort' => $this->sortBy,
            'direction' => $this->sortDirection,
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getVisibleTermIds(): array
    {
        return $this->getTermsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->forPage($this->getPage(), $this->taxonomyTermListing->perPage())
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }

    protected function taxonomyLabel(): string
    {
        return $this->taxonomyKey()->label();
    }

    protected function taxonomyLabelPlural(): string
    {
        return $this->taxonomyKey()->pluralLabel();
    }

    protected function taxonomyAdminTemplate(): TaxonomyAdminTemplateEnum
    {
        return $this->taxonomyKey()->adminTemplate();
    }

    abstract protected function taxonomyKey(): TaxonomyKeyEnum;

    abstract protected function getTermsQuery(): Builder;

    abstract protected function createTerm(): void;

    abstract protected function updateTerm(): void;

    abstract protected function deleteTerm(): void;

    /**
     * @param array<int, int> $selectedIds
     * @return mixed
     */
    abstract protected function handleBulkAction(array $selectedIds): mixed;

    abstract protected function bulkActionSuccessMessage(mixed $result): string;
}
