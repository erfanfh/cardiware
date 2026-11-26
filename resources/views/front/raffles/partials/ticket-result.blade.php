@php
    use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
    use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;

    $record = $ticket->winnerRecords
        ->sortByDesc(fn ($winner) => $winner->isEffectivePrimary() ? 1 : 0)
        ->first();

    [$label, $classes] = match (true) {
        !$record => ['—', 'text-slate-300 dark:text-slate-600'],
        !$record->isEffectivePrimary() && $record->kind === DrawWinnerKindEnum::RESERVE => ['Reserve winner', 'text-sky-600 dark:text-sky-300'],
        $record->status === DrawWinnerStatusEnum::PENDING => ['Winner; awaiting confirmation', 'text-amber-600 dark:text-amber-300'],
        $record->status === DrawWinnerStatusEnum::CONFIRMED => ['Confirmed winner', 'text-sky-600 dark:text-sky-300'],
        $record->status === DrawWinnerStatusEnum::DELIVERED => ['Prize delivered', 'text-emerald-600 dark:text-emerald-300'],
        $record->status === DrawWinnerStatusEnum::REJECTED => ['Decline prize', 'text-red-500 dark:text-red-300'],
        $record->status === DrawWinnerStatusEnum::EXPIRED => ['Claim deadline expired', 'text-slate-500 dark:text-slate-400'],
        $record->status === DrawWinnerStatusEnum::REPLACED => ['Replaced', 'text-slate-500 dark:text-slate-400'],
        default => [$record->status->label(), 'text-slate-500 dark:text-slate-400'],
    };
@endphp

<span class="font-extrabold {{ $classes }}">{{ $label }}</span>
