<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Domain\Notification\Models\AccountAnnouncement;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\CalendarUtils;
use Throwable;

class AccountAnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('account-announcement.manage');
        $filters = $request->validate([
            'title' => ['nullable', 'string', 'max:180'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);
        $query = AccountAnnouncement::query()
            ->when($filters['title'] ?? null, fn (Builder $query, string $title) => $query->where('title', 'like', '%'.$title.'%'));
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return view('admin.notification.account-announcements.index', [
            'announcements' => $query->latest('id')->paginate(20)->withQueryString(),
            'hasActiveFilters' => $request->filled('title') || $request->filled('is_active'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('account-announcement.manage');

        return view('admin.notification.account-announcements.form', ['announcement' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('account-announcement.manage');
        AccountAnnouncement::query()->create($this->validatedData($request));

        return to_route('admin.account-announcements.index')->with('success', 'اطلاعیه حساب کاربری ایجاد شد.');
    }

    public function edit(AccountAnnouncement $accountAnnouncement): View
    {
        Gate::authorize('account-announcement.manage');

        return view('admin.notification.account-announcements.form', ['announcement' => $accountAnnouncement]);
    }

    public function update(Request $request, AccountAnnouncement $accountAnnouncement): RedirectResponse
    {
        Gate::authorize('account-announcement.manage');
        $accountAnnouncement->update($this->validatedData($request));

        return to_route('admin.account-announcements.index')->with('success', 'اطلاعیه حساب کاربری ذخیره شد.');
    }

    public function destroy(AccountAnnouncement $accountAnnouncement): RedirectResponse
    {
        Gate::authorize('account-announcement.manage');
        $accountAnnouncement->delete();

        return to_route('admin.account-announcements.index')->with('success', 'اطلاعیه حساب کاربری حذف شد.');
    }

    private function validatedData(Request $request): array
    {
        $input = $request->all();
        foreach (['starts_at', 'ends_at'] as $field) {
            $input[$field] = $this->gregorianDate($request->input($field), $field);
        }

        return Validator::make($input, [
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::in(array_keys(AccountAnnouncement::TYPES))],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', Rule::when($request->filled('starts_at'), 'after:starts_at')],
        ], [], [
            'title' => 'عنوان', 'body' => 'متن اطلاعیه', 'type' => 'نوع اطلاعیه',
            'is_active' => 'وضعیت نمایش', 'starts_at' => 'شروع نمایش', 'ends_at' => 'پایان نمایش',
        ])->validate();
    }

    private function gregorianDate(mixed $value, string $field): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = CalendarUtils::convertNumbers(trim($value), true);
            if (preg_match('/^(\d{4})\/(\d{2})\/(\d{2}) ([01]\d|2[0-3]):[0-5]\d$/D', $value, $parts)) {
                try {
                    if (CalendarUtils::checkDate((int) $parts[1], (int) $parts[2], (int) $parts[3])) {
                        return gregorian_date($value, 'Y-m-d H:i:s', 'Y/m/d H:i');
                    }
                } catch (Throwable) {
                    // Out-of-range dates must produce a validation error, not a server error.
                }
            }
        }

        throw ValidationException::withMessages([$field => 'تاریخ و ساعت شمسی معتبر وارد کنید.']);
    }
}
