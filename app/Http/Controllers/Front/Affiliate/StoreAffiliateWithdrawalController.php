<?php

namespace App\Http\Controllers\Front\Affiliate;

use App\Application\Affiliate\Actions\RequestAffiliateWithdrawalAction;
use App\Domain\Affiliate\Models\Affiliate;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class StoreAffiliateWithdrawalController extends Controller
{
    public function __invoke(
        Request $request,
        RequestAffiliateWithdrawalAction $action,
    ): RedirectResponse {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'destination' => ['nullable', 'string', 'max:200'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.required' => 'مبلغ برداشت را وارد کنید.',
            'amount.integer' => 'مبلغ برداشت باید عدد باشد.',
        ]);

        $affiliate = Affiliate::query()
            ->where('user_id', $request->user()->getKey())
            ->firstOrFail();

        try {
            $action->execute(
                affiliate: $affiliate,
                amount: (int) $validated['amount'],
                destination: $validated['destination'] ?? null,
                note: $validated['note'] ?? null,
            );
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'ثبت درخواست برداشت با خطا مواجه شد؛ لطفا دوباره تلاش کنید.');
        }

        return back()->with('success', 'درخواست برداشت شما ثبت شد و پس از بررسی پرداخت می‌شود.');
    }
}
