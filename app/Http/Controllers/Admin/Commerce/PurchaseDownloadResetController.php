<?php

namespace App\Http\Controllers\Admin\Commerce;

use App\Application\Commerce\Actions\Access\ResetPurchaseAccessDownloadsAction;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PurchaseDownloadResetController extends Controller
{
    public function index(Request $request): View {
        Gate::authorize('purchase-access.reset-downloads');

        $validated = $request->validate([
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ]);

        $selectedUser = null;
        $selectedUserOption = [];
        $purchaseAccesses = collect();

        if (!empty($validated['user_id'])) {
            $selectedUser = User::query()->findOrFail((int)$validated['user_id']);

            $selectedUserOption = [[
                'value' => (string)$selectedUser->getKey(),
                'label' => $this->userLabel($selectedUser),
            ]];

            $purchaseAccesses = PurchaseAccess::query()
                ->withoutGlobalScope(ActivePurchaseAccessScope::class)
                ->where('user_id', $selectedUser->getKey())
                ->with([
                    'product' => function (BelongsTo $query): void {
                        $query->withoutGlobalScopes()->withTrashed();
                    },
                    'productSaleItem' => function (BelongsTo $query): void {
                        $query
                            ->withTrashed()
                            ->with('files');
                    },
                    'downloads:id,purchase_access_id,product_sale_item_file_id',
                ])
                ->withCount('downloads')
                ->latest('id')
                ->get();

        }

        return view('admin.commerce.purchase-download-resets.index', compact(
            'selectedUser',
            'selectedUserOption',
            'purchaseAccesses',
        ));
    }

    public function reset(
        Request $request,
        int $purchaseAccess,
        ResetPurchaseAccessDownloadsAction $resetPurchaseAccessDownloadsAction,
    ): RedirectResponse {
        Gate::authorize('purchase-access.reset-downloads');

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ]);

        $access = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($purchaseAccess)
            ->where('user_id', (int)$validated['user_id'])
            ->firstOrFail();

        $deletedDownloadsCount = $resetPurchaseAccessDownloadsAction->execute($access);

        $message = $deletedDownloadsCount > 0
            ? "محدودیت دانلود با موفقیت ریست شد و {$deletedDownloadsCount} دانلود قبلی حذف شد."
            : 'این دسترسی دانلودی برای ریست کردن نداشت.';

        return redirect()
            ->route('admin.purchase-download-resets.index', [
                'user_id' => $access->user_id,
            ])
            ->with('success', $message)
            ->with('success_title', 'ریست دانلود');
    }

    private function userLabel(User $user): string
    {
        $displayName = $user->display_name ?: trim($user->first_name . ' ' . $user->last_name);
        $email = $user->email ?: 'بدون ایمیل';

        return "{$displayName} ({$user->mobile} | {$email})";
    }
}
