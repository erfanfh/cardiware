<?php

namespace App\Application\Support\Listings;

use App\Infrastructure\Support\Listings\BaseListing;
use Illuminate\Database\Eloquent\Builder;

class TicketListing extends BaseListing
{
    public function filters(): array
    {
        return [
            'number',
            'user_name',
            'user_email',
            'user_mobile',
            'title',
            'message',
            'status',
            'under_review',
            'department_id',
            'product_id',
            'created_at_from',
            'created_at_to',
            'last_message_at_from',
            'last_message_at_to',
        ];
    }

    /**
     * ترتیب پیش‌فرض لیست پشتیبانی: اولویت VIP بالاتر، تیکت‌های محصول رایگان آخر.
     */
    protected function applySorting(): void
    {
        $this->query->orderBySupportPriority();
        parent::applySorting();
        $this->query->latestActivity();
    }

    protected function allowedSorts(): array
    {
        return [
            'last_message_at' => 'last_message_at',
            'created_at' => 'created_at',
            'number' => 'number',
        ];
    }

    protected function filterNumber(mixed $value): void
    {
        $this->query->where('number', $value);
    }

    protected function filterUserName(mixed $value): void
    {
        $this->query->whereHas('user', function (Builder $query) use ($value): void {
            $query->where('display_name', 'like', '%' . $value . '%');
        });
    }

    protected function filterUserEmail(mixed $value): void
    {
        $this->query->whereHas('user', function (Builder $query) use ($value): void {
            $query->where('email', 'like', '%' . $value . '%');
        });
    }

    protected function filterUserMobile(mixed $value): void
    {
        $this->query->whereHas('user', function (Builder $query) use ($value): void {
            $query->where('mobile', 'like', '%' . $value . '%');
        });
    }

    protected function filterTitle(mixed $value): void
    {
        $this->query->where('title', 'like', '%' . $value . '%');
    }

    protected function filterMessage(mixed $value): void
    {
        $this->query->whereHas('messages', function (Builder $query) use ($value): void {
            $query->where('body', 'like', '%' . $value . '%');
        });
    }

    protected function filterStatus(mixed $value): void
    {
        $this->query->where('status', $value);
    }

    protected function filterUnderReview(mixed $value): void
    {
        // ۱ = فقط تیکت‌های «در حال بررسی»، ۰ = بقیه
        $this->query->where('is_under_review', (int) $value === 1);
    }

    protected function filterDepartmentId(mixed $value): void
    {
        $this->query->where('department_id', $value);
    }

    protected function filterProductId(mixed $value): void
    {
        $this->query->where('product_id', $value);
    }

    protected function filterCreatedAtFrom(mixed $value): void
    {
        $this->query->whereDate('created_at', '>=', $value);
    }

    protected function filterCreatedAtTo(mixed $value): void
    {
        $this->query->whereDate('created_at', '<=', $value);
    }

    protected function filterLastMessageAtFrom(mixed $value): void
    {
        $this->query->whereDate('last_message_at', '>=', $value);
    }

    protected function filterLastMessageAtTo(mixed $value): void
    {
        $this->query->whereDate('last_message_at', '<=', $value);
    }
}
