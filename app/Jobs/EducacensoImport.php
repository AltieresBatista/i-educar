<?php

namespace App\Jobs;

use App\Models\EducacensoImport as EducacensoImportModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Queue;

class EducacensoImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var EducacensoImportModel
     */
    private $educacensoImport;

    /**
     * @var string
     */
    private $importString;

    /**
     * Create a new job instance.
     *
     * @param EducacensoImportModel $educacensoImport
     * @param $importString
     */
    public function __construct(EducacensoImportModel $educacensoImport, $importString)
    {
        $this->educacensoImport = $educacensoImport;
        $this->importString = $importString;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // todo Implementar lógica da importação

        $educacensoImport = $this->educacensoImport;
        $educacensoImport->finished = true;
        $educacensoImport->save();
    }
}
