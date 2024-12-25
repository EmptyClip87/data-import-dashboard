<?php

namespace App\Jobs;

use App\Imports\ImportFactory;
use App\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ProcessFileImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importType;
    protected $fileKey;
    protected $originalFilePath;
    protected $originalFileName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($importType, $fileKey, $originalFilePath, $originalFileName)
    {
        $this->importType = $importType;
        $this->fileKey = $fileKey;
        $this->originalFilePath = $originalFilePath;
        $this->originalFileName = $originalFileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $import = Import::create([
            'user_id' => auth()->id(),
            'import_type' => $this->importType,
            'file' => $this->fileKey,
            'original_file_name' => $this->originalFileName,
            'status' => 'in-progress',
        ]);
        session(['current_import_id' => $import->id]);

        try {
            $importClass = ImportFactory::create($this->fileKey);
            Excel::import($importClass, $this->originalFilePath);

            $import->status = 'successful';
            $import->save();
        } catch (\Exception $e) {
            $import->status = 'unsuccessful';
            $import->save();

            throw $e;
        }
    }
}
