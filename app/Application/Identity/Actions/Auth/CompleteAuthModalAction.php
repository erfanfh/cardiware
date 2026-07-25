<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Commerce\Actions\Cart\ClaimGuestCartForUserAction;
use App\Application\Identity\DTOs\CompleteAuthModalResultDTO;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Models\User;
use Throwable;

final class CompleteAuthModalAction
{
    public function __construct(
        private readonly ClaimGuestCartForUserAction $claimGuestCartForUserAction,
    )
    {
    }

    public function execute(
        User  $user,
        ?Cart $guestCart,
        bool  $claimCurrentCart,
        bool  $openCheckoutAfterAuth,
    ): CompleteAuthModalResultDTO
    {
        $warningMessage = null;

        if ($claimCurrentCart && $guestCart) {
            try {
                $this->claimGuestCartForUserAction->execute(
                    user: $user,
                    guestCart: $guestCart,
                );
            } catch (Throwable $exception) {
                report($exception);

                /*
                 * احراز هویت موفق بوده است؛ بنابراین شکست انتقال
                 * سبد نباید ورود کاربر را ناموفق نمایش دهد.
                 */
                $warningMessage = 'ورود شما انجام شد، اما انتقال سبد خرید با مشکل مواجه شد. لطفا سبد خرید را بررسی کنید.';
            }
        }

        if ($openCheckoutAfterAuth) {
            session()->flash(
                'open_checkout_after_auth',
                true
            );
        }

        return new CompleteAuthModalResultDTO(
            warningMessage: $warningMessage,
        );
    }
}
