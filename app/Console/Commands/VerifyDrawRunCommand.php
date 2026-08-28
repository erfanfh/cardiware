<?php

namespace App\Console\Commands;

use App\Application\Raffle\Actions\Draw\VerifyDrawRunAction;
use App\Domain\Raffle\Models\DrawRun;
use Illuminate\Console\Command;

class VerifyDrawRunCommand extends Command
{
    protected $signature = 'raffles:verify-draw {run : DrawRun id or public token}';
    protected $description = 'Recompute and verify a completed raffle draw run.';

    public function handle(VerifyDrawRunAction $verify): int
    {
        $value = (string)$this->argument('run');
        $run = DrawRun::query()
            ->where('public_token', $value)
            ->when(ctype_digit($value), fn($query) => $query->orWhere('id', (int)$value))
            ->first();

        if (!$run) {
            $this->error('Draw run not found.');
            return self::FAILURE;
        }

        $this->info("Verifying draw run #{$run->id} ({$run->public_token})...");
        $result = $verify->execute($run);

        $this->table(['Check', 'Result'], [
            ['Seed commitment', $result->seedCommitmentValid ? 'OK' : 'FAILED'],
            ['Eligible ticket count', $result->eligibleTicketCountValid ? 'OK' : 'FAILED'],
            ['Eligible set hash', $result->eligibleSetHashValid ? 'OK' : 'FAILED'],
            ['Winner set', $result->winnerSetValid ? 'OK' : 'FAILED'],
        ]);

        if ($result->valid) {
            $this->info('Draw run is fully reproducible and valid.');
            return self::SUCCESS;
        }

        foreach ($result->errors as $error) {
            $this->error($error);
        }

        return self::FAILURE;
    }
}
