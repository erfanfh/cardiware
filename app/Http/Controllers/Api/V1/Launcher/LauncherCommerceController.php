<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\ClaimFreeSaleItemAction;
use App\Application\Launcher\Exceptions\LauncherCommerceException;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Application\Launcher\Actions\AddLauncherModToCartAction;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Launcher\Models\LauncherCheckoutSession;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModGrant;
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
            // دسترسی امانی VIP مالکیت نیست؛ کاربر همچنان می‌تواند نسخه را
            // بخرد تا دسترسی دائمی بگیرد.
            ->owned()
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

    /**
     * خرید تکی ماد: لانچر نشست پرداخت می‌گیرد، مرورگر را باز می‌کند و پس از
     * پرداخت با checkout/consume گرانت ماد را می‌پرسد.
     */
    public function modCheckout(Request $request, int $mod): JsonResponse
    {
        $data = validator($request->all(), [
            'return_uri' => ['required', Rule::in(['mwlauncher://purchase-complete'])],
        ])->validate();

        $item = LauncherMod::query()
            ->whereKey($mod)
            ->published()
            ->firstOrFail();

        abort_unless($item->isPurchasableAvailable(), 422, 'این ماد به صورت تکی فروخته نمی‌شود.');

        $existingGrant = LauncherModGrant::query()
            ->where('user_id', $request->user()->getKey())
            ->where('launcher_mod_id', $item->getKey())
            ->first();

        if ($existingGrant instanceof LauncherModGrant) {
            return response()->json([
                'message' => 'این ماد قبلاً برای حساب شما فعال شده است.',
                'data' => [
                    'launcher_mod_id' => (int)$item->getKey(),
                    'mod_grant_source' => $existingGrant->source,
                ],
            ]);
        }

        abort_unless(
            AddLauncherModToCartAction::userHasProductAccess($item, $request->user()),
            403,
            'برای خرید این ماد ابتدا باید خودِ محصول را داشته باشید.',
        );

        $device = $request->attributes->get('launcher_device');
        abort_unless($device instanceof LauncherDevice, 401);

        $rawState = Str::random(64);
        $expiresAt = now()->addMinutes(
            max(1, (int)config('launcher.checkout_session_ttl_minutes', 15))
        );

        $session = LauncherCheckoutSession::query()->create([
            'user_id' => $request->user()->getKey(),
            'launcher_device_id' => $device->getKey(),
            // ستون product_sale_item_id اجباری است؛ نسخه‌ی مادرِ ماد ثبت می‌شود.
            'product_sale_item_id' => $item->product_sale_item_id,
            'launcher_mod_id' => $item->getKey(),
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

            // تا یک روز بعد از مهلت هم نتیجه تحویل می‌شود (ورود + پرداخت ممکن است طول بکشد).
            if (!$session || !$session->canDeliverResult()) {
                throw LauncherCommerceException::checkoutExpired();
            }

            if ($session->launcher_mod_id !== null) {
                $grant = LauncherModGrant::query()
                    ->where('user_id', $request->user()->getKey())
                    ->where('launcher_mod_id', $session->launcher_mod_id)
                    ->first();

                if (!$grant) {
                    throw LauncherCommerceException::purchaseNotCompleted();
                }

                $session->forceFill(['consumed_at' => now()])->save();

                return [
                    'launcher_mod_id' => (int) $session->launcher_mod_id,
                    'mod_grant_source' => $grant->source,
                ];
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
