<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Application\Loyalty\Actions\AdjustPointsAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\DTOs\AdjustPointsDTO;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Loyalty\AdjustAudienceMemberPointsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class AdjustAudienceMemberPointsController extends Controller
{
    /** @throws Throwable */
    public function __invoke(
        AdjustAudienceMemberPointsRequest $request,
        User $user,
        GetOrCreatePointAccountAction $getOrCreatePointAccount,
        AdjustPointsAction $adjustPoints,
    ): RedirectResponse {
        Gate::authorize('loyalty-account.adjust');

        $data = $request->validated();
        $direction = PointEntryDirectionEnum::from($data['direction']);
        $account = $getOrCreatePointAccount->execute($user);

        $entry = $adjustPoints->execute(new AdjustPointsDTO(
            account: $account,
            direction: $direction,
            amount: (int)$data['amount'],
            reason: 'manual_adjustment',
            idempotencyKey: $data['idempotency_key'],
            actor: $request->user(),
            description: $data['note'],
            metadata: [
                'source' => 'admin',
                'manual_adjustment' => true,
            ],
        ));

        $verb = $direction->isCredit() ? 'افزایش' : 'کاهش';

        return redirect()
            ->route('admin.loyalty-members.show', $user)
            ->with('success', sprintf(
                '%s امتیاز کاربر با موفقیت انجام شد. موجودی جدید: %s امتیاز.',
                $verb,
                number_format((int)$entry->balance_after),
            ))
            ->with('success_title', 'اصلاح امتیاز انجام شد');
    }
}
