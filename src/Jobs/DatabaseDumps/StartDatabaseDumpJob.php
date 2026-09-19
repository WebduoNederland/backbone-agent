<?php

namespace WebduoNederland\BackboneAgent\Jobs\DatabaseDumps;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;
use WebduoNederland\BackboneAgent\Actions\DatabaseDumps\StartDatabaseDump;
use WebduoNederland\BackboneAgent\Clients\BackboneApi;
use WebduoNederland\BackboneAgent\Enums\DatabaseDumpStatus;

class StartDatabaseDumpJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $id,
        protected string $fileName,
        protected array $excludeTables,
        protected array $sftpCredentials,
    ) {
        $this->onQueue(config()->string('backbone-agent.database_dumps.queue'));
    }

    public function handle(StartDatabaseDump $startDatabaseDump): void
    {
        $startDatabaseDump->start($this->id, $this->fileName, $this->excludeTables, $this->sftpCredentials);
    }

    public function tries(): int
    {
        return 1;
    }

    public function failed(?Throwable $exception): void
    {
        app(BackboneApi::class)->http()
            ->patch('v1/database-dumps/'.$this->id.'/status', [
                'status' => DatabaseDumpStatus::Failed,
                'message' => $exception?->getMessage() ?? '',
            ]);
    }
}
