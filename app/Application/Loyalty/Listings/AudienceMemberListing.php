<?php

namespace App\Application\Loyalty\Listings;

use App\Infrastructure\Support\Listings\BaseListing;
use Illuminate\Database\Eloquent\Builder;

class AudienceMemberListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'id',
            'query',
            'mobile',
            'email',
            'status',
            'role',
            'has_account',
            'balance_from',
            'balance_to',
            'lifetime_earned_from',
            'lifetime_earned_to',
            'created_at_from',
            'created_at_to',
        ];
    }

    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'users.created_at',
            'balance' => 'points_balance',
            'lifetime_earned' => 'points_lifetime_earned',
            'lifetime_spent' => 'points_lifetime_spent',
        ];
    }

    public function defaultSort(): array
    {
        return [
            'sort' => 'created_at',
            'direction' => 'desc',
        ];
    }

    public function perPage(): int
    {
        return 15;
    }

    protected function filterId(mixed $value): void
    {
        $this->query->where('users.id', (int)$value);
    }

    protected function filterQuery(mixed $value): void
    {
        $term = trim((string)$value);

        $this->query->where(function (Builder $query) use ($term): void {
            $query
                ->where('users.first_name', 'like', "%{$term}%")
                ->orWhere('users.last_name', 'like', "%{$term}%")
                ->orWhere('users.display_name', 'like', "%{$term}%")
                ->orWhere('users.username', 'like', "%{$term}%")
                ->orWhere('users.mobile', 'like', "%{$term}%")
                ->orWhere('users.email', 'like', "%{$term}%");
        });
    }

    protected function filterMobile(mixed $value): void
    {
        $this->query->where('users.mobile', 'like', '%' . trim((string)$value) . '%');
    }

    protected function filterEmail(mixed $value): void
    {
        $this->query->where('users.email', 'like', '%' . trim((string)$value) . '%');
    }

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('users.status', $value);
    }

    protected function filterRole(mixed $value): void
    {
        $this->query->where('users.role_id', (int)$value);
    }

    protected function filterHasAccount(mixed $value): void
    {
        if ((string)$value === '1') {
            $this->query->whereNotNull('loyalty_account.id');
        } elseif ((string)$value === '0') {
            $this->query->whereNull('loyalty_account.id');
        }
    }

    protected function filterBalanceFrom(mixed $value): void
    {
        $this->query->whereRaw('COALESCE(loyalty_account.balance, 0) >= ?', [(int)$value]);
    }

    protected function filterBalanceTo(mixed $value): void
    {
        $this->query->whereRaw('COALESCE(loyalty_account.balance, 0) <= ?', [(int)$value]);
    }

    protected function filterLifetimeEarnedFrom(mixed $value): void
    {
        $this->query->whereRaw('COALESCE(loyalty_account.lifetime_earned, 0) >= ?', [(int)$value]);
    }

    protected function filterLifetimeEarnedTo(mixed $value): void
    {
        $this->query->whereRaw('COALESCE(loyalty_account.lifetime_earned, 0) <= ?', [(int)$value]);
    }

    protected function filterCreatedAtFrom(mixed $value): void
    {
        $this->query->whereDate('users.created_at', '>=', $value);
    }

    protected function filterCreatedAtTo(mixed $value): void
    {
        $this->query->whereDate('users.created_at', '<=', $value);
    }
}
