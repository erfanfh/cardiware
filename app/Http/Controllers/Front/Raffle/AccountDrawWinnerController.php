<?php

namespace App\Http\Controllers\Front\Raffle;

use App\Application\Raffle\Actions\Winner\ConfirmDrawWinnerAction;
use App\Application\Raffle\Actions\Winner\RejectDrawWinnerAction;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawWinner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Raffle\ClaimDrawPrizeRequest;
use App\Http\Requests\Front\Raffle\DeclineDrawPrizeRequest;
use Illuminate\Http\RedirectResponse;

class AccountDrawWinnerController extends Controller
{
    public function claim(
        ClaimDrawPrizeRequest $request,
        DrawWinner $drawWinner,
        ConfirmDrawWinnerAction $confirm,
    ): RedirectResponse {
        abort_unless((int) $drawWinner->user_id === (int) $request->user()->getKey(), 404);
        abort_unless($drawWinner->isEffectivePrimary(), 404);

        try {
            $confirm->execute($drawWinner, $request->user(), [
                ...$request->safe()->only(['contact_name', 'mobile', 'postal_code', 'address']),
                'ip' => $request->ip(),
            ]);
        } catch (\DomainException|\LogicException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('account.raffles')
            ->with('success', 'دریافت جایزه تأیید شد. اطلاعات تحویل برای پیگیری ادمین ثبت شد.');
    }

    public function decline(
        DeclineDrawPrizeRequest $request,
        DrawWinner $drawWinner,
        RejectDrawWinnerAction $reject,
    ): RedirectResponse {
        abort_unless((int) $drawWinner->user_id === (int) $request->user()->getKey(), 404);
        abort_unless($drawWinner->isEffectivePrimary(), 404);

        if ($drawWinner->status !== DrawWinnerStatusEnum::PENDING) {
            return back()->with('error', 'انصراف توسط کاربر فقط پیش از تأیید دریافت جایزه امکان‌پذیر است.');
        }

        $reason = trim((string) $request->validated('reason')) ?: 'انصراف از دریافت جایزه توسط کاربر';
        try {
            $reject->execute($drawWinner, $reason, $request->user());
        } catch (\DomainException|\LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('account.raffles')
            ->with('success', 'انصراف شما ثبت شد و جایزه طبق قوانین به نفر ذخیره منتقل می‌شود.');
    }
}
