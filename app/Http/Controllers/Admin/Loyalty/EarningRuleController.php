<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Application\Loyalty\Actions\EarningRule\CreateEarningRuleAction;
use App\Application\Loyalty\Actions\EarningRule\DeleteEarningRuleAction;
use App\Application\Loyalty\Actions\EarningRule\UpdateEarningRuleAction;
use App\Application\Loyalty\Earning\Support\EarningRuleDefinitionCatalog;
use App\Application\Loyalty\Listings\EarningRuleListing;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Loyalty\IndexEarningRuleRequest;
use App\Http\Requests\Admin\Loyalty\SaveEarningRuleRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class EarningRuleController extends Controller
{
    public function index(IndexEarningRuleRequest $request, EarningRuleListing $listing): View
    {
        Gate::authorize('loyalty-rule.list');

        $validated = $request->validated();

        $earningRules = $listing
            ->handle(EarningRule::query()->withCount('entries'), $validated)
            ->paginate($listing->perPage())
            ->withQueryString();

        return view('admin.loyalty.earning-rules.index', [
            'earningRules' => $earningRules,
            'types' => $this->typeOptions(),
            'hasActiveFilters' => $listing->hasActiveFilters($validated),
            'bulkActionOptions' => DefaultBulkActions::set()->toOptions(),
        ]);
    }

    public function create(EarningRuleDefinitionCatalog $definitions): View
    {
        Gate::authorize('loyalty-rule.create');

        return view('admin.loyalty.earning-rules.create', $this->formData(null, $definitions));
    }

    /** @throws Throwable */
    public function store(SaveEarningRuleRequest $request, CreateEarningRuleAction $action): RedirectResponse
    {
        Gate::authorize('loyalty-rule.create');

        $earningRule = $action->execute($request->validated());

        return redirect()
            ->route('admin.earning-rules.edit', $earningRule)
            ->with('success', 'قانون امتیاز با موفقیت ایجاد شد.');
    }

    public function edit(EarningRule $earningRule, EarningRuleDefinitionCatalog $definitions): View
    {
        Gate::authorize('loyalty-rule.edit');

        return view('admin.loyalty.earning-rules.edit', $this->formData($earningRule, $definitions));
    }

    /** @throws Throwable */
    public function update(
        SaveEarningRuleRequest $request,
        EarningRule $earningRule,
        UpdateEarningRuleAction $action,
    ): RedirectResponse {
        Gate::authorize('loyalty-rule.edit');

        $earningRule = $action->execute($earningRule, $request->validated());

        return redirect()
            ->route('admin.earning-rules.edit', $earningRule)
            ->with('success', 'قانون امتیاز با موفقیت بروزرسانی شد.');
    }

    /** @throws Throwable */
    public function destroy(EarningRule $earningRule, DeleteEarningRuleAction $action): RedirectResponse
    {
        Gate::authorize('loyalty-rule.delete');

        $action->execute($earningRule);

        return redirect()
            ->route('admin.earning-rules.index')
            ->with('success', 'قانون امتیاز با موفقیت حذف شد.');
    }

    private function formData(?EarningRule $earningRule, EarningRuleDefinitionCatalog $definitions): array
    {
        $defaults = [];

        foreach (EarningRuleTypeEnum::cases() as $type) {
            $defaults[$type->value] = $definitions->defaultConfig($type);
        }

        return [
            'earningRule' => $earningRule,
            'types' => $this->typeOptions(),
            'ruleDefaults' => $defaults,
        ];
    }

    private function typeOptions(): array
    {
        return collect(EarningRuleTypeEnum::cases())
            ->mapWithKeys(fn(EarningRuleTypeEnum $type) => [$type->value => $type->label()])
            ->all();
    }
}
