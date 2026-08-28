<?php

namespace App\Application\Raffle\Jobs;

use App\Application\Raffle\Actions\Draw\ExecuteDrawRunAction;
use App\Application\Raffle\Actions\Draw\MarkDrawRunFailedAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

final class ExecuteDrawRunJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 3600;
    public bool $failOnTimeout = true;

    public function __construct(public readonly int $drawRunId)
    {
        $this->onConnection('redis-raffle');
        $this->onQueue('raffle');
    }

    public function handle(ExecuteDrawRunAction $action): void
    {
        $action->execute($this->drawRunId);
    }

    public function failed(?Throwable $exception): void
    {
        app(MarkDrawRunFailedAction::class)->execute(
            $this->drawRunId,
            $exception?->getMessage() ?: 'Draw job failed without an exception message.',
        );
    }

    public function tags(): array
    {
        return ['raffle', 'draw-run:' . $this->drawRunId];
    }
}
