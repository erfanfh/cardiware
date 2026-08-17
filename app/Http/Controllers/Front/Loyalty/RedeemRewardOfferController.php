<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Actions\Reward\RedeemRewardOfferAction;
use App\Application\Loyalty\Exceptions\InsufficientPointsException;
use App\Application\Loyalty\Exceptions\RewardOfferUnavailableException;
use App\Application\Loyalty\Exceptions\RewardRedemptionIdempotencyConflictException;
use App\Application\Loyalty\Exceptions\RewardRedemptionLimitExceededException;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Loyalty\RedeemRewardOfferRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class RedeemRewardOfferController extends Controller
{
    public function __invoke(
        RedeemRewardOfferRequest $request,
        RewardOffer $rewardOffer,
        RedeemRewardOfferAction $action,
    ): RedirectResponse {
        try {
            $redemption = $action->execute($rewardOffer, $request->user(), $request->idempotencyKey());

            return redirect()->to(route('account.loyalty') . '#rewards')
                ->with('success', 'پاداش با موفقیت دریافت شد. کد تخفیف اختصاصی شما آماده استفاده است.')
                ->with('reward_redemption_token', $redemption->public_token);
        } catch (InsufficientPointsException) {
            return redirect()->to(route('account.loyalty') . '#rewards')
                ->with('error', 'امتیاز قابل استفاده شما برای دریافت این پاداش کافی نیست.');
        } catch (RewardOfferUnavailableException|RewardRedemptionLimitExceededException $exception) {
            return redirect()->to(route('account.loyalty') . '#rewards')
                ->with('error', $exception->getMessage());
        } catch (RewardRedemptionIdempotencyConflictException $exception) {
            report($exception);
            return redirect()->to(route('account.loyalty') . '#rewards')
                ->with('error', 'درخواست دریافت پاداش معتبر نیست. صفحه را تازه‌سازی و دوباره تلاش کنید.');
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->to(route('account.loyalty') . '#rewards')
                ->with('error', 'در دریافت پاداش مشکلی رخ داد. لطفا دوباره تلاش کنید.');
        }
    }
}
