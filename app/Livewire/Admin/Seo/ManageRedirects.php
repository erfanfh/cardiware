<?php

namespace App\Livewire\Admin\Seo;

use App\Application\Seo\Actions\Redirect\CreateRedirectAction;
use App\Application\Seo\Actions\Redirect\DeleteRedirectAction;
use App\Application\Seo\Actions\Redirect\DeleteSelectedRedirectsAction;
use App\Application\Seo\Actions\Redirect\NormalizeRedirectPathAction;
use App\Application\Seo\Actions\Redirect\SetRedirectActiveStateAction;
use App\Application\Seo\Actions\Redirect\UpdateRedirectAction;
use App\Domain\Seo\Enums\RedirectStatusCodeEnum;
use App\Domain\Seo\Models\Redirect;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class ManageRedirects extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $activeFilter = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public array $selectedIds = [];

    public string $bulkAction = '';

    public ?int $editingId = null;

    public string $sourcePath = '';

    public string $targetUrl = '';

    public string $statusCode = '301';

    public string $isActive = '1';

    public string $editingFormat = 'plain';

    public ?int $highlightedId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'activeFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'performDeleteRedirect' => 'performDeleteRedirect',
    ];

    public function mount(): void
    {
        Gate::authorize('redirect.manage');
    }

    protected function rules(): array
    {
        return [
            'sourcePath' => [
                'required',
                'string',
                'max:2048',
                'not_in:/',
            ],
            'targetUrl' => [
                Rule::requiredIf(
                    fn (): bool => (int) $this->statusCode !== RedirectStatusCodeEnum::Gone->value
                ),
                'nullable',
                'string',
                'max:8192',
            ],
            'statusCode' => [
                'required',
                Rule::in(
                    array_map(
                        fn (RedirectStatusCodeEnum $status): string => (string) $status->value,
                        RedirectStatusCodeEnum::cases()
                    )
                ),
            ],
            'isActive' => [
                'required',
                Rule::in(['0', '1']),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'sourcePath.required' => 'مسیر مبدا ریدایرکت الزامی است.',
            'sourcePath.max' => 'مسیر مبدا نباید بیشتر از ۲۰۴۸ کاراکتر باشد.',
            'sourcePath.not_in' => 'برای مسیر اصلی سایت (/) امکان ثبت ریدایرکت وجود ندارد.',
            'targetUrl.required' => 'برای این نوع ریدایرکت، آدرس مقصد الزامی است.',
            'targetUrl.max' => 'آدرس مقصد بیش از حد طولانی است.',
            'statusCode.required' => 'کد وضعیت را انتخاب کنید.',
            'statusCode.in' => 'کد وضعیت انتخاب‌شده معتبر نیست.',
            'isActive.required' => 'وضعیت ریدایرکت را مشخص کنید.',
            'isActive.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
        ];
    }

    public function getIsEditingProperty(): bool
    {
        return filled($this->editingId);
    }

    public function getStatusCodeOptionsProperty(): array
    {
        return collect(RedirectStatusCodeEnum::cases())
            ->mapWithKeys(fn (RedirectStatusCodeEnum $status) => [
                (string) $status->value => $status->value . ' - ' . $status->label(),
            ])
            ->all();
    }

    public function getStatusFilterOptionsProperty(): array
    {
        return [
            '' => 'همه کدهای وضعیت',
            ...$this->statusCodeOptions,
        ];
    }

    public function getActiveFilterOptionsProperty(): array
    {
        return [
            '' => 'همه وضعیت‌ها',
            '1' => 'فقط فعال',
            '0' => 'فقط غیرفعال',
        ];
    }

    public function getBulkActionOptionsProperty(): array
    {
        return [
            '' => 'انتخاب عملیات',
            'activate' => 'فعال کردن',
            'deactivate' => 'غیرفعال کردن',
            'delete' => 'حذف گروهی',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetListState();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetListState();
    }

    public function updatedActiveFilter(): void
    {
        $this->resetListState();
    }

    public function updatedStatusCode(): void
    {
        if ((int) $this->statusCode === RedirectStatusCodeEnum::Gone->value) {
            $this->targetUrl = '';
            $this->resetValidation('targetUrl');
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->activeFilter = '';

        $this->resetListState();
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        Gate::authorize('redirect.manage');

        $this->sourcePath = app(NormalizeRedirectPathAction::class)
            ->execute($this->sourcePath);

        $this->targetUrl = trim($this->targetUrl);

        $validated = $this->validate();

        $this->ensureSourcePathIsUnique();
        $this->ensureTargetDoesNotLoop();

        $data = [
            'source_path' => $validated['sourcePath'],
            'target_url' => $validated['targetUrl'] ?? null,
            'status_code' => (int) $validated['statusCode'],
            'is_active' => (bool) ((int) $validated['isActive']),
        ];

        if ($this->isEditing) {
            $this->updateRedirect($data);

            return;
        }

        $this->createRedirect($data);
    }

    protected function createRedirect(array $data): void
    {
        $redirect = app(CreateRedirectAction::class)->execute($data);

        $this->highlightedId = $redirect->id;

        $this->resetForm();
        $this->resetPage();

        session()->flash('success', 'ریدایرکت با موفقیت ایجاد شد.');
    }

    protected function updateRedirect(array $data): void
    {
        abort_unless($this->editingId, 404);

        $redirect = Redirect::query()->findOrFail($this->editingId);

        $redirect = app(UpdateRedirectAction::class)
            ->execute($redirect, $data);

        $this->highlightedId = $redirect->id;

        $this->resetForm();

        session()->flash('success', 'ریدایرکت با موفقیت به‌روزرسانی شد.');
    }

    public function startEditing(int $id): void
    {
        Gate::authorize('redirect.manage');

        $redirect = Redirect::query()->findOrFail($id);

        $this->editingId = $redirect->id;
        $this->sourcePath = $redirect->source_path;
        $this->targetUrl = (string) $redirect->target_url;
        $this->statusCode = (string) $redirect->status_code->value;
        $this->isActive = $redirect->is_active ? '1' : '0';
        $this->editingFormat = $redirect->format;

        $this->resetValidation();

        $this->dispatch('redirect-edit-started');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();

        $this->dispatch('redirects-scroll-to-list');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->sourcePath = '';
        $this->targetUrl = '';
        $this->statusCode = '301';
        $this->isActive = '1';
        $this->editingFormat = 'plain';

        $this->resetValidation();
    }

    public function sortByColumn(string $column, string $direction = 'asc'): void
    {
        if (!array_key_exists($column, $this->sortableColumns())) {
            return;
        }

        if ($this->sortBy === $column) {
            if ($this->sortDirection === $direction) {
                $this->sortBy = 'created_at';
                $this->sortDirection = 'desc';
            } else {
                $this->sortDirection = $direction;
            }
        } else {
            $this->sortBy = $column;
            $this->sortDirection = $direction;
        }

        $this->resetPage();
        $this->clearSelection();

        $this->dispatch('redirects-scroll-to-list');
    }

    public function clearSelection(): void
    {
        $this->selectedIds = [];
    }

    /**
     * @throws Throwable
     */
    public function applyBulkAction(): void
    {
        Gate::authorize('redirect.manage');

        if ($this->bulkAction === '' || empty($this->selectedIds)) {
            return;
        }

        $selectedIds = $this->normalizedSelectedIds();

        if ($selectedIds === []) {
            return;
        }

        if ($this->bulkAction === 'delete') {
            $affectedCount = app(DeleteSelectedRedirectsAction::class)
                ->execute($selectedIds);

            if ($this->editingId !== null && in_array($this->editingId, $selectedIds, true)) {
                $this->resetForm();
            }

            $message = "{$affectedCount} ریدایرکت با موفقیت حذف شد.";
        } elseif (in_array($this->bulkAction, ['activate', 'deactivate'], true)) {
            $isActive = $this->bulkAction === 'activate';

            $affectedCount = app(SetRedirectActiveStateAction::class)
                ->execute($selectedIds, $isActive);

            if ($this->editingId !== null && in_array($this->editingId, $selectedIds, true)) {
                $this->isActive = $isActive ? '1' : '0';
            }

            $stateLabel = $isActive ? 'فعال' : 'غیرفعال';
            $message = "{$affectedCount} ریدایرکت با موفقیت {$stateLabel} شد.";
        } else {
            return;
        }

        $this->clearSelection();
        $this->bulkAction = '';

        session()->flash('success', $message);
    }

    /**
     * @throws Throwable
     */
    public function performDeleteRedirect($id = null): void
    {
        Gate::authorize('redirect.manage');

        if (is_array($id)) {
            $id = $id['id'] ?? null;
        }

        $id = (int) $id;

        if (!$id) {
            return;
        }

        $redirect = Redirect::query()->findOrFail($id);
        $redirectId = $redirect->id;

        app(DeleteRedirectAction::class)->execute($redirect);

        if ($this->editingId === $redirectId) {
            $this->resetForm();
        }

        $this->selectedIds = array_values(
            array_diff($this->selectedIds, [$redirectId])
        );

        session()->flash('success', 'ریدایرکت با موفقیت حذف شد.');
    }

    public function toggleActive(int $id): void
    {
        Gate::authorize('redirect.manage');

        $redirect = Redirect::query()->findOrFail($id);
        $newState = !$redirect->is_active;

        app(SetRedirectActiveStateAction::class)
            ->execute([$redirect->id], $newState);

        if ($this->editingId === $redirect->id) {
            $this->isActive = $newState ? '1' : '0';
        }

        $stateLabel = $newState ? 'فعال' : 'غیرفعال';

        session()->flash('success', "ریدایرکت با موفقیت {$stateLabel} شد.");
    }

    public function nextPage($pageName = 'page'): void
    {
        $this->setPage(($this->getPage($pageName) ?? 1) + 1, $pageName);
        $this->dispatch('redirects-scroll-to-list');
    }

    public function previousPage($pageName = 'page'): void
    {
        $currentPage = (int) ($this->getPage($pageName) ?? 1);

        if ($currentPage > 1) {
            $this->setPage($currentPage - 1, $pageName);
        }

        $this->dispatch('redirects-scroll-to-list');
    }

    public function gotoPage($page, $pageName = 'page'): void
    {
        $this->setPage($page, $pageName);
        $this->dispatch('redirects-scroll-to-list');
    }

    protected function getRedirectsQuery(): Builder
    {
        return Redirect::query()
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('source_path', 'like', '%' . $search . '%')
                        ->orWhere('target_url', 'like', '%' . $search . '%');
                });
            })
            ->when(
                $this->statusFilter !== ''
                    && in_array(
                        (int) $this->statusFilter,
                        array_map(
                            fn (RedirectStatusCodeEnum $status): int => $status->value,
                            RedirectStatusCodeEnum::cases()
                        ),
                        true
                    ),
                fn (Builder $query) => $query->where('status_code', (int) $this->statusFilter)
            )
            ->when(
                in_array($this->activeFilter, ['0', '1'], true),
                fn (Builder $query) => $query->where('is_active', (bool) ((int) $this->activeFilter))
            );
    }

    protected function sortableColumns(): array
    {
        return [
            'source_path' => 'source_path',
            'status_code' => 'status_code',
            'is_active' => 'is_active',
            'created_at' => 'created_at',
        ];
    }

    protected function safeSortBy(): string
    {
        return array_key_exists($this->sortBy, $this->sortableColumns())
            ? $this->sortBy
            : 'created_at';
    }

    protected function safeSortDirection(): string
    {
        return $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }

    protected function normalizedSelectedIds(): array
    {
        return collect($this->selectedIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function resetListState(): void
    {
        $this->resetPage();
        $this->clearSelection();
        $this->dispatch('redirects-scroll-to-list');
    }

    /**
     * @throws ValidationException
     */
    protected function ensureSourcePathIsUnique(): void
    {
        $query = Redirect::query()
            ->where('source_path_hash', md5($this->sourcePath));

        if ($this->editingId !== null) {
            $query->where('id', '!=', $this->editingId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'sourcePath' => 'برای این مسیر مبدا قبلا یک ریدایرکت ثبت شده است.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function ensureTargetDoesNotLoop(): void
    {
        $status = RedirectStatusCodeEnum::from((int) $this->statusCode);

        if ($status->isGone()) {
            return;
        }

        $target = trim($this->targetUrl);
        $isRelative = str_starts_with($target, '/');
        $scheme = strtolower((string) parse_url($target, PHP_URL_SCHEME));
        $isHttpUrl = filter_var($target, FILTER_VALIDATE_URL) !== false
            && in_array($scheme, ['http', 'https'], true);

        if ($isRelative && !str_starts_with($target, '//')) {
            $targetPath = app(NormalizeRedirectPathAction::class)->execute($target);

            if ($targetPath === $this->sourcePath) {
                throw ValidationException::withMessages([
                    'targetUrl' => 'مسیر مقصد نمی‌تواند با مسیر مبدا یکسان باشد؛ این حالت باعث حلقه ریدایرکت می‌شود.',
                ]);
            }

            return;
        }

        if (!$isHttpUrl) {
            return;
        }

        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $targetHost = strtolower((string) parse_url($target, PHP_URL_HOST));

        if ($appHost === '' || $targetHost !== $appHost) {
            return;
        }

        $targetPath = app(NormalizeRedirectPathAction::class)->execute($target);

        if ($targetPath === $this->sourcePath) {
            throw ValidationException::withMessages([
                'targetUrl' => 'آدرس مقصد به همین مسیر در سایت اشاره می‌کند و باعث حلقه ریدایرکت می‌شود.',
            ]);
        }
    }

    public function render(): View
    {
        $items = $this->getRedirectsQuery()
            ->orderBy($this->safeSortBy(), $this->safeSortDirection())
            ->paginate(20);

        return view('livewire.admin.seo.manage-redirects', [
            'items' => $items,
            'statusCodeOptions' => $this->statusCodeOptions,
            'statusFilterOptions' => $this->statusFilterOptions,
            'activeFilterOptions' => $this->activeFilterOptions,
            'bulkActionOptions' => $this->bulkActionOptions,
        ]);
    }
}
