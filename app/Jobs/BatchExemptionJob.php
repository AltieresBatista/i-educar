<?php

namespace App\Jobs;

use App\Jobs\Concerns\ShouldNotificate;
use App\Services\Exemption\BatchExemptionService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Throwable;

class BatchExemptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldNotificate;

    /**
     * @var array
     */
    private $importArray;

    /**
     * @var string
     */
    private $databaseConnection;

    /**
     * @var BatchExemptionService
     */
    private $batchExemptionService;

    /**
     * @var User
     */
    private $user;


    public function __construct(BatchExemptionService $batchExemptionService, $databaseConnection, User $user)
    {
        $this->batchExemptionService = $batchExemptionService;
        $this->databaseConnection = $databaseConnection;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        DB::setDefaultConnection($this->databaseConnection);
        DB::beginTransaction();

        try {
            $this->batchExemptionService->handle();
        } catch (Throwable $exception) {
            $this->notificateError();

            DB::rollBack();
            return;
        }

        $this->notificateSuccess();
        DB::commit();
    }

    public function tags()
    {
        return [
            $this->databaseConnection,
            'batch-exemption'
        ];
    }

    public function getSuccessMessage()
    {
        return 'O processo de Dispensa em lote foi finalizado.';
    }

    public function getNotificationUrl()
    {
        return url()->route('exemption-list.index');
    }

    public function getUser()
    {
        return $this->user;
    }
}
