<?php

namespace App\Http\Controllers\Front\Launcher;

use App\Application\Launcher\Actions\AddLauncherModToCartAction;
use App\Domain\Launcher\Models\LauncherCheckoutSession;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LauncherCheckoutEntryController extends Controller
{
    public function __invoke(
        Request $request,
        string $checkoutSession,
        string $state,
        AddLauncherModToCartAction $addModToCart,
    ): RedirectResponse {
        $session = LauncherCheckoutSession::query()
            ->whereKey($checkoutSession)
            ->where('state_hash', hash('sha256', $state))
            ->where('user_id', $request->user()->getKey())
            ->whereNull('consumed_at')
            ->with('saleItem.product', 'mod')
            ->firstOrFail();

        abort_if($session->expires_at->isPast(), 410);

        $request->session()->put('launcher_checkout_state', $state);

        // خرید تکی ماد: مستقیم به سبد خرید اضافه می‌شود و کاربر پرداخت را
        // همان‌جا تمام می‌کند؛ لانچر با checkout/consume نتیجه را می‌گیرد.
        if ($session->launcher_mod_id !== null && $session->mod !== null) {
            try {
                $addModToCart->execute($session->mod, $request->user());
            } catch (\DomainException $exception) {
                // ماد قبلاً خریده شده، محصول مادر را ندارد یا غیرقابل خرید شده.
                return redirect()->to(front_route('cart.index'))->with('error', $exception->getMessage());
            }

            return redirect()->to(front_route('cart.index'))
                ->with('success', 'ماد به سبد خرید اضافه شد؛ پس از پرداخت در لانچر فعال می‌شود.');
        }

        return redirect()->to($session->saleItem->product->url);
    }
}
