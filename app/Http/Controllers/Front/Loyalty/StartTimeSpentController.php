<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\TimeSpent\TimeSpentSessionService;
use App\Application\Loyalty\Earning\Actions\ResolveActiveEarningRuleAction;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StartTimeSpentController extends Controller
{
    public function __construct(
        private readonly ResolveActiveEarningRuleAction $resolveRule,
        private readonly TimeSpentSessionService $service,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'target_type' => ['required', 'string', 'in:product,post'],
            'target_id' => ['required', 'integer'],
        ]);

        $rule = $this->resolveRule->execute(
            EarningRuleTypeEnum::TIME_SPENT,
            now()
        );

        abort_unless($rule, 404);

        $allowedTargets = data_get($rule->config, 'targets', ['product', 'post']);

        abort_unless(in_array($data['target_type'], $allowedTargets, true), 422);

        $session = $this->service->start(
            $request->user(),
            $data['target_type'],
            $data['target_id'],
            $rule
        );

        return response()->json([
            'token' => $session->token,
        ]);
    }
}
