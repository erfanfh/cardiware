<?php

namespace Tests\Feature\Launcher;

use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Jobs\VerifyRemoteDownloadFileIntegrityJob;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class RemoteDownloadIntegrityQueueTest extends TestCase
{
    public function test_remote_integrity_verification_uses_its_own_long_running_queue(): void
    {
        $job = new VerifyRemoteDownloadFileIntegrityJob(
            'product',
            'App\\Models\\ExampleFile',
            123,
            'https://downloads.example.test/package.zip',
            'integrity-lock',
        );

        $this->assertSame(VerifyRemoteDownloadFileIntegrityJob::CONNECTION, $job->connection);
        $this->assertSame(VerifyRemoteDownloadFileIntegrityJob::QUEUE, $job->queue);
        $this->assertSame(
            'redis-download-integrity',
            config('horizon.defaults.supervisor-download-integrity.connection'),
        );
        $this->assertSame(
            ['download-integrity'],
            config('horizon.defaults.supervisor-download-integrity.queue'),
        );
        $this->assertGreaterThan(
            $job->timeout,
            config('queue.connections.redis-download-integrity.retry_after'),
        );
    }

    public function test_an_integrity_job_already_waiting_on_default_is_moved_without_running(): void
    {
        Queue::fake();

        $job = new VerifyRemoteDownloadFileIntegrityJob(
            'product',
            'App\\Models\\ExampleFile',
            123,
            'https://downloads.example.test/package.zip',
            'integrity-lock',
        );

        $queuedJob = Mockery::mock(QueueJob::class);
        $queuedJob->shouldReceive('getQueue')->once()->andReturn('default');
        $job->setJob($queuedJob);

        $resolver = Mockery::mock(RemoteDownloadFileMetadataResolver::class);
        $resolver->shouldNotReceive('verifyAndPersist');

        $job->handle($resolver);

        Queue::assertPushedOn('download-integrity', VerifyRemoteDownloadFileIntegrityJob::class);
        Queue::assertPushed(
            VerifyRemoteDownloadFileIntegrityJob::class,
            fn (VerifyRemoteDownloadFileIntegrityJob $movedJob): bool => $movedJob->connection === 'redis-download-integrity'
                && $movedJob->fileId === 123,
        );
    }
}
