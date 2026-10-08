<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Application\Audit\Support\AuditEventPresenter;
use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Domain\Audit\Models\AuditEvent;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use Throwable;

class AuditEventController extends Controller
{
    public function index(Request $request, AuditEventPresenter $presenter): View
    {
        Gate::authorize('audit.view');

        $filters = $request->validate([
            'type' => ['nullable', Rule::in(array_column(AuditEventTypeEnum::cases(), 'value'))],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'string', 'max:64'],
            'actor_id' => ['nullable', 'integer', 'min:1'],
            'q' => ['nullable', 'string', 'max:160'],
            'from' => ['nullable', 'string', 'max:10'],
            'to' => ['nullable', 'string', 'max:10'],
        ]);

        $from = $this->parseJalaliDate($filters['from'] ?? null, 'from');
        $to = $this->parseJalaliDate($filters['to'] ?? null, 'to');
        if ($from && $to && $to->lt($from)) {
            throw ValidationException::withMessages(['to' => 'تاریخ پایان باید برابر یا بعد از تاریخ شروع باشد.']);
        }

        $query = AuditEvent::query()
            ->when($filters['type'] ?? null, fn ($query, $value) => $query->where('type', $value))
            ->when($filters['subject_type'] ?? null, fn ($query, $value) => $query->where('subject_type', $value))
            ->when($filters['subject_id'] ?? null, fn ($query, $value) => $query->where('subject_id', $value))
            ->when($filters['actor_id'] ?? null, fn ($query, $value) => $query->where('user_id', $value))
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('created_at', '<', $to->addDay()));

        if (filled($filters['q'] ?? null)) {
            $text = trim($filters['q']);
            $query->where(fn ($query) => $query
                ->where('subject_label', 'like', '%'.$text.'%')
                ->orWhere('subject_id', $text));
        }

        $events = $query->orderByDesc('id')->paginate(25)->withQueryString();
        $subjectTypes = AuditEvent::query()->distinct()->orderBy('subject_type')
            ->pluck('subject_type')->mapWithKeys(fn ($type) => [$type => $presenter->subjectTypeLabel($type)])->all();
        $eventTypes = collect(AuditEventTypeEnum::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $presenter->typeLabel($type)])->all();

        return view('admin.audit.events.index', compact('events', 'filters', 'subjectTypes', 'eventTypes', 'presenter'));
    }

    public function show(AuditEvent $auditEvent, AuditEventPresenter $presenter): View
    {
        Gate::authorize('audit.view');

        $timeline = AuditEvent::query()
            ->where('subject_type', $auditEvent->subject_type)
            ->where('subject_id', $auditEvent->subject_id)
            ->where('id', '<=', $auditEvent->id)
            ->orderByDesc('id')->limit(10)->get();

        return view('admin.audit.events.show', compact('auditEvent', 'presenter', 'timeline'));
    }

    private function parseJalaliDate(?string $value, string $field): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        $normalized = strtr(trim($value), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        try {
            if (! preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $normalized)) {
                throw new \InvalidArgumentException;
            }

            [$year, $month, $day] = array_map('intval', explode('/', $normalized));

            return (new Jalalian($year, $month, $day))->toCarbon()->startOfDay();
        } catch (Throwable) {
            throw ValidationException::withMessages([$field => 'تاریخ را با فرمت شمسی ۱۴۰۵/۰۷/۱۶ وارد کنید.']);
        }
    }
}
