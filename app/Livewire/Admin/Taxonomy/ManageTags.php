<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\CreateTagAction;
use App\Application\Taxonomy\Actions\HandleTagBulkAction;
use App\Application\Taxonomy\Actions\UpdateTagAction;
use App\Application\Taxonomy\Listings\TagListing;
use App\Application\Taxonomy\Support\TagBulkActions;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class ManageTags extends Component
{
    use WithPagination;

    protected TagListing $tagListing;

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
        'page' => ['except' => 1],
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

    public function mount(): void
    {
        $default = $this->tagListing->defaultSort();

        $this->sortBy = $default['sort'];
        $this->sortDirection = $default['direction'];
    }

    public function boot(TagListing $tagListing): void
    {
        $this->tagListing = $tagListing;
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

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearSelection();
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

    public function cancelEditing(): void
    {
        $this->resetForm();
        $this->dispatch('taxonomy-scroll-to-list');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
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

    /**
     * @throws Throwable
     */
    /**
     * @throws Throwable
     */
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

        $handler = app(HandleTagBulkAction::class);

        $result = $handler->execute(
            action: $this->bulkAction,
            ids: $selectedIds,
        );

        if (
            $this->editingId !== null &&
            in_array($this->editingId, $selectedIds, true) &&
            $this->bulkAction === 'delete'
        ) {
            $this->resetForm();
        }

        $this->clearSelection();
        $this->bulkAction = '';

        session()->flash('success', $handler->successMessage($result));
    }

    public function sortByColumn(string $column, string $direction = 'asc'): void
    {
        if (!array_key_exists($column, $this->tagListing->sortable())) {
            return;
        }

        if ($this->sortBy === $column) {
            if ($this->sortDirection === $direction) {
                $default = $this->tagListing->defaultSort();

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

    protected function getTermsQuery(): Builder
    {
        $query = TaxonomyTerm::tags();

        return $this->tagListing->handle($query, $this->listingFilters());
    }

    /**
     * @return array<int, int>
     */
    protected function getVisibleTermIds(): array
    {
        return $this->getTermsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->forPage($this->getPage(), $this->tagListing->perPage())
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }

    protected function taxonomyTypeValue(): string|int
    {
        return TaxonomyTermTypeEnum::TAG->value;
    }

    public function render(): View
    {
        $terms = $this->getTermsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->tagListing->perPage());

        return view('livewire.admin.taxonomy.manage-tags', [
            'items' => $terms,
            'taxonomyLabel' => 'برچسب',
            'taxonomyLabelPlural' => 'برچسب‌ها',
            'bulkActionOptions' => TagBulkActions::set()->toOptions(),
        ]);
    }
}
