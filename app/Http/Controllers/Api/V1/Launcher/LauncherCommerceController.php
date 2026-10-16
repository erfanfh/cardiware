<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\ClaimFreeSaleItemAction;
use App\Application\Launcher\Exceptions\LauncherCommerceException;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Launcher\Models\LauncherCheckoutSession;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LauncherCommerceController extends Controller
{
    public function claim(
        Request $request,
        int $saleItem,
        ClaimFreeSaleItemAction $action,
    ): JsonResponse {
        $idempotencyKey = (string)$request->header('Idempotency-Key');
        validator(
            ['idempotency_key' => $idempotencyKey],
            ['idempotency_key' => ['required', 'uuid']],
        )->validate();

        $access = $action->execute($request->user(), $saleItem, $idempotencyKey);

        return response()->json([
            'message' => 'دسترسی رایگان محصول فعال شد.',
            'data' => ['access_id' => $access->id],
        ], $access->wasRecentlyCreated ? 201 : 200);
    }

    public function checkout(Request $request, int $saleItem): JsonResponse
    {
        $data = validator($request->all(), [
            'return_uri' => ['required', Rule::in(['mwlauncher://purchase-complete'])],
        ])->validate();

        $item = ProductSaleItem::query()
            ->whereKey($saleItem)
            ->where('launcher_enabled', true)
            ->firstOrFail();

        $existingAccess = PurchaseAccess::query()
            ->withoutGlobalScopes()
            ->where('user_id', $request->user()->getKey())
            ->where('product_sale_item_id', $saleItem)
            ->latest('id')
            ->get()
            ->first(fn(PurchaseAccess $access): bool => $access->isUsableForLauncher());

        if ($existingAccess instanceof PurchaseAccess) {
            return response()->json([
                'message' => 'این نسخه فروش قبلاً برای حساب شما ثبت شده است.',
                'data' => ['purchase_access_id' => $existingAccess->getKey()],
            ]);
        }

        abort_unless($item->isPurchasableAvailable(), 422, 'این نسخه فروش در حال حاضر موجود نیست.');

        $device = $request->attributes->get('launcher_device');
        abort_unless($device instanceof LauncherDevice, 401);

        $rawState = Str::random(64);
        $expiresAt = now()->addMinutes(
            max(1, (int)config('launcher.checkout_session_ttl_minutes', 15))
        );

        $session = LauncherCheckoutSession::query()->create([
            'user_id' => $request->user()->getKey(),
            'launcher_device_id' => $device->getKey(),
            'product_sale_item_id' => $saleItem,
            'state_hash' => hash('sha256', $rawState),
            'expires_at' => $expiresAt,
        ]);

        $browserUrl = URL::temporarySignedRoute(
            'launcher.checkout.entry',
            $expiresAt,
            ['checkoutSession' => $session->getKey(), 'state' => $rawState],
        );

        return response()->json([
            'data' => [
                'browser_url' => $browserUrl,
                'state' => $rawState,
                'return_uri' => $data['return_uri'],
                'expires_at' => $expiresAt->toISOString(),
            ],
        ], 201);
    }

    public function consume(Request $request): JsonResponse
    {
        $data = validator($request->all(), [
            'state' => ['required', 'string', 'min:32', 'max:255'],
        ])->validate();

        $device = $request->attributes->get('launcher_device');

        $result = DB::transaction(function () use ($request, $data, $device): array {
            $session = LauncherCheckoutSession::query()
                ->where('state_hash', hash('sha256', $data['state']))
                ->where('user_id', $request->user()->getKey())
                ->where('launcher_device_id', $device->getKey())
                ->lockForUpdate()
                ->first();

            if (!$session || $session->consumed_at !== null || $session->expires_at->isPast()) {
                throw LauncherCommerceException::checkoutExpired();
            }

            $access = PurchaseAccess::query()
                ->withoutGlobalScopes()
                ->where('user_id', $request->user()->getKey())
                ->where('product_sale_item_id', $session->product_sale_item_id)
                ->latest('id')
                ->get()
                ->first(fn(PurchaseAccess $item): bool => $item->isUsableForLauncher());

            if (!$access instanceof PurchaseAccess) {
                throw LauncherCommerceException::purchaseNotCompleted();
            }

            $session->forceFill(['consumed_at' => now()])->save();

            return [
                'purchase_access_id' => $access->id,
                'sale_item_id' => $session->product_sale_item_id,
            ];
        });

        return response()->json([
            'message' => 'خرید تأیید شد؛ کتابخانه باید نوسازی شود.',
            'data' => $result,
        ]);
    }
}
