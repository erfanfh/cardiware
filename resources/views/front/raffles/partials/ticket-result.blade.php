@php
    use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
    use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;

    $record = $ticket->winnerRecords
        ->sortByDesc(fn ($winner) => $winner->isEffectivePrimary() ? 1 : 0)
        ->first();

    [$label, $classes] = match (true) {
        !$record => ['—', 'text-slate-300 dark:text-slate-600'],
        !$record->isEffectivePrimary() && $record->kind === DrawWinnerKindEnum::RESERVE => ['نفر ذخیره', 'text-sky-600 dark:text-sky-300'],
        $record->status === DrawWinnerStatusEnum::PENDING => ['برنده؛ منتظر تأیید', 'text-amber-600 dark:text-amber-300'],
        $record->status === DrawWinnerStatusEnum::CONFIRMED => ['برنده تأییدشده', 'text-sky-600 dark:text-sky-300'],
        $record->status === DrawWinnerStatusEnum::DELIVERED => ['جایزه تحویل‌شده', 'text-emerald-600 dark:text-emerald-300'],
        $record->status === DrawWinnerStatusEnum::REJECTED => ['انصراف از جایزه', 'text-red-500 dark:text-red-300'],
        $record->status === DrawWinnerStatusEnum::EXPIRED => ['مهلت دریافت منقضی‌شده', 'text-slate-500 dark:text-slate-400'],
        $record->status === DrawWinnerStatusEnum::REPLACED => ['جایگزین‌شده', 'text-slate-500 dark:text-slate-400'],
        default => [$record->status->label(), 'text-slate-500 dark:text-slate-400'],
    };
@endphp

<span class="font-extrabold {{ $classes }}">{{ $label }}</span>
