<?php

namespace App\Http\Controllers\Admin\Support;

use App\Domain\Support\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * علامت‌گذاری/برداشتن علامت «در حال بررسی» تیکت توسط پشتیبان.
 * تیکت‌های علامت‌خورده در دستهٔ اختصاصی لیست تیکت‌ها دیده می‌شوند.
 */
class TicketUnderReviewController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('ticket.change-status');

        $mark = (bool) $request->validate(['mark' => ['required', 'boolean']])['mark'];

        $ticket->forceFill([
            'is_under_review' => $mark,
            'under_review_marked_at' => $mark ? ($ticket->under_review_marked_at ?? now()) : null,
        ])->save();

        return redirect()
            ->back()
            ->with('success', $mark
                ? 'تیکت علامت‌گذاری شد: در حال بررسی.'
                : 'علامت بررسی تیکت برداشته شد.');
    }
}
