<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\PointConversion\Actions\ConvertPointsToWalletAction;
use App\Application\Loyalty\PointConversion\DTOs\ConvertPointsToWalletDTO;
use App\Application\Loyalty\PointConversion\Exceptions\PointConversionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Loyalty\ConvertPointsToWalletRequest;
use Illuminate\Http\RedirectResponse;

class ConvertPointsToWalletController extends Controller
{
    public function __invoke(
        ConvertPointsToWalletRequest $request,
        ConvertPointsToWalletAction $convertPoints,
    ): RedirectResponse {
        try {
            $conversion = $convertPoints->execute(new ConvertPointsToWalletDTO(
                user: $request->user(),
                points: $request->integer('points'),
                idempotencyKey: $request->string('idempotency_key')->toString(),
            ));
        } catch (PointConversionException $exception) {
            return redirect()
                ->to(route('account.loyalty').'#convert-points')
                ->withInput($request->only('points'))
                ->with('point_conversion_error', $exception->getMessage())
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->to(route('account.loyalty').'#convert-points')
            ->with('point_conversion_success', [
                'points' => (int) $conversion->points,
                'wallet_amount' => (int) $conversion->wallet_amount,
            ])
            ->with('success', sprintf(
                '%s امتیاز با موفقیت به %s تومان موجودی کیف پول تبدیل شد.',
                number_format((int) $conversion->points),
                number_format((int) $conversion->wallet_amount),
            ));
    }
}
