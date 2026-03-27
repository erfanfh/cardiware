<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageTags extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public array $selectedIds = [];
    public ?string $bulkAction = null;

    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';

    public ?int $deletingId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('tags', 'slug')->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string'],
        ];
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

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            if ($this->sortDirection === 'asc') {
                $this->sortBy = 'created_at';
                $this->sortDirection = 'desc';
                return;
            }

            $this->sortDirection = 'asc';
            return;
        }

        $this->sortBy = $column;
        $this->sortDirection = 'asc';
    }

    public function save(): void
    {
        if ($this->isEditing) {
            $this->updateTag();
            return;
        }

        $this->createTag();
    }

    public function createTag(): void
    {
        $validated = $this->validate();

        $validated['slug'] = $this->normalizeSlug(
            name: $validated['name'],
            slug: $validated['slug'] ?? null,
        );

        $validated['type'] = TaxonomyTermTypeEnum::TAG;

        TaxonomyTerm::query()->create($validated);

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'برچسب با موفقیت ایجاد شد.');
    }

    public function startEditing(int $id): void
    {
        $tag = TaxonomyTerm::tags()->findOrFail($id);

        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug ?? '';
        $this->description = $tag->description ?? '';

        $this->resetValidation();
    }

    public function updateTag(): void
    {
        abort_unless($this->editingId, 404);

        $validated = $this->validate();

        $validated['slug'] = $this->normalizeSlug(
            name: $validated['name'],
            slug: $validated['slug'] ?? null,
        );

        $tag = TaxonomyTerm::tags()->findOrFail($this->editingId);
        $tag->update($validated);

        $this->resetForm();

        session()->flash('success', 'برچسب با موفقیت به‌روزرسانی شد.');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function deleteTag(): void
    {
        abort_unless($this->deletingId, 404);

        $tag = TaxonomyTerm::tags()->findOrFail($this->deletingId);
        $tag->delete();

        if ($this->editingId === $tag->id) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$tag->id]));
        $this->deletingId = null;

        session()->flash('success', 'برچسب با موفقیت حذف شد.');
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
        $visibleIds = $this->getVisibleTagIds();

        if (empty($visibleIds)) {
            return;
        }

        $allSelected = count(array_intersect($visibleIds, $this->selectedIds)) === count($visibleIds);

        if ($allSelected) {
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
        if (!$this->bulkAction || empty($this->selectedIds)) {
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
            $this->bulkAction = null;

            session()->flash('success', 'برچسب‌های انتخاب‌شده با موفقیت حذف شدند.');
        }
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';

        $this->resetValidation();
    }

    protected function getTagsQuery(): Builder
    {
        return TaxonomyTerm::tags()
            ->when($this->search !== '', function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            });
    }

    protected function getVisibleTagIds(): array
    {
        return $this->getTagsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->forPage($this->getPage(), $this->perPage)
            ->pluck('id')
            ->all();
    }

    protected function normalizeSlug(string $name, ?string $slug = null): string
    {
        $value = filled($slug) ? $slug : $name;

        return str($value)->slug()->value();
    }

    public function render(): View
    {
        $tags = $this->getTagsQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.taxonomy.manage-tags', [
            'tags' => $tags,
        ]);
    }
}
