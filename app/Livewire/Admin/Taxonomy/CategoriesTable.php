<?php

namespace App\Livewire\Admin\Taxonomy;

use AllowDynamicProperties;
use App\Application\Taxonomy\Actions\BuildTreeAction;
use App\Application\Taxonomy\Actions\BulkDeleteAction;
use App\Application\Taxonomy\Actions\GeneratePathAction;
use App\Application\Taxonomy\Actions\GenerateUniqueSlugAction;
use App\Application\Taxonomy\Actions\RecordSlugHistoryAction;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Str;
use Livewire\WithPagination;

#[AllowDynamicProperties] class CategoriesTable extends Component
{
    use WithPagination;

    public $editingId;
    public $title, $slug, $parent_id, $description;
    public $search = '';
    public $selectedIds = [];
    public $bulkAction = '';
    protected $listeners = ['refresh' => '$refresh'];

    /**
     * @param GeneratePathAction $generatePathAction
     * @param BuildTreeAction $buildTreeAction
     * @param RecordSlugHistoryAction $recordSlugHistoryAction
     * @param GenerateUniqueSlugAction $generateUniqueSlugAction
     * @return void
     */
    public function boot(GeneratePathAction       $generatePathAction,
                         BuildTreeAction          $buildTreeAction,
                         RecordSlugHistoryAction  $recordSlugHistoryAction,
                         GenerateUniqueSlugAction $generateUniqueSlugAction): void
    {
        $this->generatePathAction = $generatePathAction;
        $this->buildTreeAction = $buildTreeAction;
        $this->recordSlugHistoryAction = $recordSlugHistoryAction;
        $this->generateUniqueSlugAction = $generateUniqueSlugAction;
    }

    /**
     * @return array
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'parent_id' => ['nullable', Rule::exists('taxonomy_terms', 'id')],
            'description' => 'nullable|string',
        ];
    }

    /**
     * @return void
     */
    public function mount(): void
    {

        if (request()->has('edit')) {
            $this->edit(request('edit'));
        }
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        $allTerms = TaxonomyTerm::where('type', 'category')
            ->with(['children', 'parent'])
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->whereNull('parent_id')
            ->orderBy('id', 'asc')
            ->get();

        $treeStructure = $this->buildTreeAction->execute($allTerms);

        collect($treeStructure)->each(function ($term) {
            $term->path = $this->generatePathAction->execute($term);
        });


        $editingTerm = $this->editingId ? TaxonomyTerm::find($this->editingId) : null;

        return view('livewire.admin.taxonomy.categories-table', [
            'terms' => $treeStructure,
            'editingTerm' => $editingTerm,
        ])
            ->extends('admin.layout')
            ->section('content');
    }


    /**
     * @return void
     */
    public function store(): void
    {
        $this->validate();

        $inputSlug = trim($this->slug ?? '');
        $userEnteredSlug = !empty($inputSlug);
        $slugBase = $userEnteredSlug ? $inputSlug : $this->title;

        $finalSlug = $this->generateUniqueSlugAction->execute(
            Str::slug($slugBase),
            TaxonomyTermTypeEnum::CATEGORY,
            null
        );

        $data = [
            'title' => $this->title,
            'slug' => $finalSlug,
            'parent_id' => $this->parent_id,
            'description' => $this->description,
            'type' => 'category',
        ];

        try {
            $term = TaxonomyTerm::create($data);
            $term->path = $this->generatePathAction->execute($term);
            $term->saveQuietly();

            session()->flash('success', 'دسته‌بندی با موفقیت ایجاد شد.');

            if ($userEnteredSlug && $finalSlug !== Str::slug($inputSlug)) {
                session()->flash('warning', "اسلاگ '{$inputSlug}' تکراری بود. مقدار یکتا '{$finalSlug}' به‌صورت خودکار تنظیم شد.");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'خطا در ایجاد دسته‌بندی: ' . $e->getMessage());
            return;
        }

        $this->resetForm();
        $this->dispatch('refresh');
    }

    /**
     * @return void
     * @throws ValidationException
     */
    public function update(): void
    {
        $this->validate();

        $term = TaxonomyTerm::find($this->editingId);
        if (!$term) {
            session()->flash('error', 'دسته‌بندی یافت نشد.');
            return;
        }

        $originalSlug = $term->slug;
        $termType = TaxonomyTermTypeEnum::CATEGORY;

        $slugToGenerateFrom = trim($this->slug ?? '') ?: $this->title;

        $finalSlug = $this->generateUniqueSlugAction->execute(
            Str::slug($slugToGenerateFrom),
            $termType,
            $this->editingId
        );

        if ($originalSlug !== $finalSlug) {

            $this->recordSlugHistoryAction->execute(
                $term,
                $termType->value,
                $originalSlug,
                $finalSlug
            );
        }

        $term->update([
            'title' => $this->title,
            'slug' => $finalSlug,
            'parent_id' => $this->parent_id,
            'description' => $this->description,
        ]);

        $this->resetForm();
        session()->flash('message', 'دسته بندی با موفقیت به روز رسانی شد.');
        $this->dispatch('refresh');
    }

    /**
     * @param $id
     * @return void
     */
    public function edit($id): void
    {
        $this->editingId = $id;
        $term = TaxonomyTerm::findOrFail($id);
        $this->title = $term->title;
        $this->slug = $term->slug;
        $this->parent_id = $term->parent_id;
        $this->description = $term->description;
    }

    public function cancelEdit()
    {
        $this->resetForm();
        return redirect()->route('admin.categories.index');
    }

    /**
     * @param $id
     * @return void
     */
    public function delete($id): void
    {
        TaxonomyTerm::findOrFail($id)->delete();
        session()->flash('success', 'دسته‌بندی با موفقیت حذف شد.');
        $this->resetForm();
        $this->dispatch('refresh');
    }

    /**
     * @return void
     */
    public function bulkDelete(): void
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'هیچ آیتمی انتخاب نشده است.');
            return;
        }

        $count = app(BulkDeleteAction::class)->execute($this->selectedIds);
        $this->selectedIds = [];
        $this->bulkAction = '';
        session()->flash('success', "{$count} دسته بندی حذف شد.");
        $this->dispatch('tagUpdated');
    }

    public bool $selectAll = false;

    /**
     * @param $value
     * @return void
     */
    public function updatedSelectAll($value): void
    {
        if ($value) {
            $allTerms = TaxonomyTerm::where('type', 'category')
                ->when($this->search, function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('slug', 'like', '%' . $this->search . '%');
                })
                ->orderBy('id', 'asc')
                ->get();

            $this->selectedIds = $allTerms->pluck('id')->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function isSelected($id): bool
    {
        return in_array($id, $this->selectedIds);
    }

    /**
     * @param $id
     * @return void
     */
    public function toggleSelect($id): void
    {
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_filter($this->selectedIds, fn($i) => $i !== $id);
        } else {
            $this->selectedIds[] = $id;
        }
    }

    /**
     * @return void
     */
    #[On('confirmBulkDelete')]
    public function proceedWithBulkDelete(): void
    {
        $this->bulkDelete();
    }

    /**
     * @return void
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return void
     */
    public function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'slug', 'parent_id', 'description', 'editingTerm']);
    }
}
