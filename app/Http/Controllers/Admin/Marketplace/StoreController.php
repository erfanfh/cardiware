<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Application\Marketplace\Actions\CreateStoreAction;
use App\Application\Marketplace\Actions\DeleteStoreAction;
use App\Application\Marketplace\Actions\UpdateStoreAction;
use App\Application\Marketplace\Listings\StoreListing;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Marketplace\IndexStoreRequest;
use App\Http\Requests\Admin\Marketplace\UpdateStoreRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param IndexStoreRequest $request
     * @param StoreListing $storeListing
     * @return View
     */
    public function index(IndexStoreRequest $request, StoreListing $storeListing): View
    {
        Gate::authorize('store.list');

        $stores = $storeListing
            ->handle(
                Store::query()
                    ->with([
                        'picture.primaryVariant',
                        'seoAnalysis'
                    ])
                    ->withCount([
                        'users',
                        'products',
                    ]),
                $request->validated()
            )
            ->paginate($storeListing->perPage())
            ->withQueryString();

        $statuses = collect(StoreStatusEnum::cases())
            ->mapWithKeys(fn(StoreStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $hasActiveFilters = $storeListing->hasActiveFilters($request->validated());

        $bulkActionOptions = DefaultBulkActions::set()->toOptions();

        return view('admin.marketplace.stores.index', compact(
            'stores',
            'statuses',
            'hasActiveFilters',
            'bulkActionOptions',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        Gate::authorize('store.create');

        [$sellerUserIds, $selectedSellerOptions] = $this->sellerSelection(null);

        return view('admin.marketplace.stores.create', compact('sellerUserIds', 'selectedSellerOptions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UpdateStoreRequest $request
     * @param CreateStoreAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(UpdateStoreRequest $request, CreateStoreAction $action): RedirectResponse
    {
        Gate::authorize('store.create');

        $store = $action->execute($request->validated());

        return redirect()->route('admin.stores.edit', $store)
            ->with('success', 'فروشنده با موفقیت ایجاد شد.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Store $store
     * @return View
     */
    public function edit(Store $store): View
    {
        Gate::authorize('store.edit');

        [$sellerUserIds, $selectedSellerOptions] = $this->sellerSelection($store);

        return view('admin.marketplace.stores.edit', compact('store', 'sellerUserIds', 'selectedSellerOptions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     * @param UpdateStoreAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateStoreRequest $request, Store $store, UpdateStoreAction $action): RedirectResponse
    {
        Gate::authorize('store.edit');

        $action->execute($store, $request->validated());

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('success', 'فروشنده با موفقیت بروزرسانی شد.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        Gate::authorize('store.delete');

        $resultDTO = app(DeleteStoreAction::class)->execute($store);

        if (!$resultDTO->status) {
            return redirect()
                ->back()
                ->with('error', $resultDTO->message);
        }

        return redirect()
            ->route('admin.stores.index')
            ->with('success', 'فروشنده با موفقیت حذف شد.');
    }

    private function sellerSelection(?Store $store): array
    {
        $sellerUserIds = old('seller_user_ids', $store?->sellers()->pluck('users.id')->all() ?? []);
        $sellerUserIds = is_array($sellerUserIds) ? $sellerUserIds : [];

        $selectedSellerOptions = User::query()
            ->whereIn('id', $sellerUserIds)
            ->get(['id', 'display_name', 'first_name', 'last_name', 'mobile', 'email'])
            ->map(function (User $user): array {
                $name = $user->display_name ?: trim($user->first_name . ' ' . $user->last_name);

                return [
                    'value' => $user->id,
                    'label' => "{$name} ({$user->mobile} | " . ($user->email ?: 'بدون ایمیل') . ')',
                ];
            })
            ->all();

        return [$sellerUserIds, $selectedSellerOptions];
    }
}
