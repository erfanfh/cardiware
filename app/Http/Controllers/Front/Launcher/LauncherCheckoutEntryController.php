<?php

namespace App\Http\Controllers\Front\Launcher;

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
    ): RedirectResponse {
        $session = LauncherCheckoutSession::query()
            ->whereKey($checkoutSession)
            ->where('state_hash', hash('sha256', $state))
            ->where('user_id', $request->user()->getKey())
            ->whereNull('consumed_at')
            ->with('saleItem.product')
            ->firstOrFail();

        abort_if($session->expires_at->isPast(), 410);

        $request->session()->put('launcher_checkout_state', $state);

        return redirect()->to($session->saleItem->product->url);
    }
}
