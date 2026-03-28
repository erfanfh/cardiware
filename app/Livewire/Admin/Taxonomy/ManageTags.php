<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Application\Taxonomy\Listings\TagListing;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageTags extends Component
{
    use WithPagination;

    protected TagListing $tagListing;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 10;

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
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique((new TaxonomyTerm())->getTable(), 'slug')
                    ->where(fn($query) => $query->where('type', $this->taxonomyTypeValue()))
                    ->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string'],
        ];
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

    public function createTerm(): void
    {
        $validated = $this->validate();

        $validated['slug'] = $this->generateUniqueSlug(
            title: $validated['title'],
            slug: $validated['slug'] ?? null,
        );

        $validated['type'] = TaxonomyTermTypeEnum::TAG;

        $term = TaxonomyTerm::query()->create($validated);

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

        $validated = $this->validate();

        $validated['slug'] = $this->generateUniqueSlug(
            title: $validated['title'],
            slug: $validated['slug'] ?? null,
            ignoreId: $this->editingId,
        );

        $term = TaxonomyTerm::tags()->findOrFail($this->editingId);
        $term->update($validated);

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

    public function applyBulkAction(): void
    {
        if ($this->bulkAction === '' || empty($this->selectedIds)) {
            return;
        }

        if ($this->bulkAction === 'delete') {
            TaxonomyTerm::tags()
                ->whereIn('id', $this->selectedIds)
                ->delete();

            if ($this->editingId && in_array($this->editingId, $this->selectedIds, true)) {
                $this->resetForm();
            }

            $this->clearSelection();
            $this->bulkAction = '';

            session()->flash('success', 'برچسب‌های انتخاب‌شده با موفقیت حذف شدند.');
        }
    }

    public function sortByColumn(string $column, string $direction = 'asc'): void
    {
        if (!array_key_exists($column, $this->tagListing->sortable())) {
            return;
        }

        if ($this->sortBy === $column) {
            if ($this->sortDirection === $direction) {
                $default = $this->tagListing->defaultSort();

                $this->sortBy = $default['sort'] ?? 'created_at';
                $this->sortDirection = $default['direction'] ?? 'desc';
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
            ->forPage($this->getPage(), $this->perPage)
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }

    protected function generateUniqueSlug(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = $this->normalizeSlug($title, $slug);

        $slugCandidate = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slugCandidate, $ignoreId)) {
            $slugCandidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slugCandidate;
    }

    protected function normalizeSlug(string $title, ?string $slug = null): string
    {
        $value = filled($slug) ? $slug : $title;

        $normalized = Str::slug($value);

        if ($normalized !== '') {
            return $normalized;
        }

        return 'term-' . Str::lower(Str::random(6));
    }

    protected function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return TaxonomyTerm::query()
            ->where('type', $this->taxonomyTypeValue())
            ->where('slug', $slug)
            ->when($ignoreId, fn(Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    protected function taxonomyTypeValue(): string|int
    {
        return TaxonomyTermTypeEnum::TAG->value;
    }

    public function render(): View
    {
        $terms = $this->getTermsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.taxonomy.manage-tags', [
            'items' => $terms,
            'taxonomyLabel' => 'برچسب',
            'taxonomyLabelPlural' => 'برچسب‌ها',
        ]);
    }
}
