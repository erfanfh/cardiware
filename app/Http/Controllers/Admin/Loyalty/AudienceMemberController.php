<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Application\Loyalty\Listings\AudienceMemberListing;
use App\Application\Loyalty\Listings\PointLedgerEntryListing;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Loyalty\IndexAudienceMemberRequest;
use App\Http\Requests\Admin\Loyalty\ShowAudienceMemberRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AudienceMemberController extends Controller
{
    public function index(IndexAudienceMemberRequest $request, AudienceMemberListing $listing): View
    {
        Gate::authorize('loyalty-account.list');

        $validated = $request->validated();

        $query = User::query()
            ->leftJoin('point_accounts as loyalty_account', 'loyalty_account.user_id', '=', 'users.id')
            ->select('users.*')
            ->addSelect([
                DB::raw('COALESCE(loyalty_account.balance, 0) as points_balance'),
                DB::raw('COALESCE(loyalty_account.lifetime_earned, 0) as points_lifetime_earned'),
                DB::raw('COALESCE(loyalty_account.lifetime_spent, 0) as points_lifetime_spent'),
            ])
            ->with(['role', 'avatar', 'pointAccount']);

        $members = $listing
            ->handle($query, $validated)
            ->paginate($listing->perPage())
            ->withQueryString();

        $roles = Role::query()->orderBy('title')->pluck('title', 'id')->all();
        $statuses = collect(UserStatusEnum::cases())
            ->mapWithKeys(fn(UserStatusEnum $status) => [$status->value => $status->label()])
            ->all();

        $stats = [
            'accounts_count' => PointAccount::query()->count(),
            'total_balance' => (int)PointAccount::query()->sum('balance'),
            'total_lifetime_earned' => (int)PointAccount::query()->sum('lifetime_earned'),
            'total_lifetime_spent' => (int)PointAccount::query()->sum('lifetime_spent'),
        ];

        $hasActiveFilters = $listing->hasActiveFilters($validated);

        return view('admin.loyalty.members.index', compact(
            'members',
            'roles',
            'statuses',
            'stats',
            'hasActiveFilters',
        ));
    }

    public function show(
        ShowAudienceMemberRequest $request,
        User $user,
        PointLedgerEntryListing $ledgerListing,
    ): View {
        Gate::authorize('loyalty-account.show');

        $user->loadMissing(['role', 'avatar', 'pointAccount']);
        $account = $user->pointAccount;
        $filters = $request->validated();

        $entriesQuery = $account
            ? PointLedgerEntry::query()->where('point_account_id', $account->id)
            : PointLedgerEntry::query()->whereRaw('1 = 0');

        $entries = $ledgerListing
            ->handle(
                $entriesQuery->with(['earningRule', 'actor', 'reference', 'reversalOf']),
                $filters,
            )
            ->paginate($ledgerListing->perPage())
            ->withQueryString();

        $types = collect(PointEntryTypeEnum::cases())
            ->mapWithKeys(fn(PointEntryTypeEnum $type) => [$type->value => $type->label()])
            ->all();

        $directions = collect(PointEntryDirectionEnum::cases())
            ->mapWithKeys(fn(PointEntryDirectionEnum $direction) => [$direction->value => $direction->label()])
            ->all();

        $hasActiveFilters = $ledgerListing->hasActiveFilters($filters);

        return view('admin.loyalty.members.show', compact(
            'user',
            'account',
            'entries',
            'types',
            'directions',
            'hasActiveFilters',
        ));
    }
}
