<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Actions\Reward\RedeemRewardOfferAction;
use App\Application\Loyalty\Exceptions\InsufficientPointsException;
use App\Application\Loyalty\Exceptions\RewardOfferUnavailableException;
use App\Application\Loyalty\Exceptions\RewardRedemptionLimitExceededException;
use App\Application\Loyalty\Exceptions\RewardRedemptionIdempotencyConflictException;
use App\Application\Raffle\Exceptions\DrawCampaignNotAcceptingTicketsException;
use App\Application\Raffle\Exceptions\DrawTicketCapacityExceededException;
use App\Application\Raffle\Exceptions\DrawUserTicketLimitExceededException;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Raffle\Models\TicketGrant;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class RedeemRewardOfferController extends Controller
{
    /** @throws Throwable */
    public function __invoke(
        Request $request,
        RewardOffer $rewardOffer,
        RedeemRewardOfferAction $redeemReward,
    ): RedirectResponse {
        $request->validate([
            'idempotency_key' => ['required', 'string', 'max:120'],
        ]);

        try {
            $redemption = $redeemReward->execute(
                rewardOffer: $rewardOffer,
                user: $request->user(),
                idempotencyKey: (string) $request->input('idempotency_key'),
            );
        } catch (InsufficientPointsException) {
            return back()->with('error', 'امتیاز شما برای دریافت این پاداش کافی نیست.');
        } catch (RewardRedemptionLimitExceededException) {
            return back()->with('error', 'سقف مجاز دریافت این پاداش تکمیل شده است.');
        } catch (RewardRedemptionIdempotencyConflictException) {
            return back()->with('error', 'شناسه این درخواست قبلا برای عملیات دیگری استفاده شده است. صفحه را تازه سازی و دوباره تلاش کنید.');
        } catch (RewardOfferUnavailableException) {
            return back()->with('error', 'این پاداش در حال حاضر قابل دریافت نیست.');
        } catch (DrawCampaignNotAcceptingTicketsException) {
            return back()->with('error', 'این قرعه کشی در حال حاضر بلیط جدید نمی پذیرد.');
        } catch (DrawTicketCapacityExceededException) {
            return back()->with('error', 'ظرفیت باقی مانده قرعه کشی برای این پاداش کافی نیست.');
        } catch (DrawUserTicketLimitExceededException) {
            return back()->with('error', 'با دریافت این پاداش از سقف مجاز بلیط شما عبور می شود.');
        }

        if (
            $rewardOffer->type === RewardTypeEnum::RAFFLE_TICKET
            && $redemption->fulfillment instanceof TicketGrant
        ) {
            return redirect()
                ->route('account.raffles')
                ->with('success', sprintf(
                    '%s با موفقیت دریافت شد و بلیط های شما صادر شدند.',
                    $rewardOffer->title,
                ))
                ->with('celebration', $rewardOffer->title);
        }

        return redirect()
            ->to(route('account.loyalty') . '#rewards')
            ->with('success', 'پاداش با موفقیت دریافت شد.')
            ->with('celebration', $rewardOffer->title);
    }
}
