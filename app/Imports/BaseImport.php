<?php

namespace App\Imports;

use App\ImportLog;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use PhpOffice\PhpSpreadsheet\Shared\Date;

abstract class BaseImport implements SkipsOnFailure

{
    use SkipsFailures;
    use RemembersRowNumber;

    protected $expectedHeaders;
    protected $importId;

    public function __construct(string $configKey)
    {
        $this->expectedHeaders = array_keys(Config::get("import_types.$configKey.headers_to_db"));
        $this->importId = Session::get('current_import_id');
    }

    /**
     * @param array $data
     * @return void
     * @throws Exception
     */
    protected function validateHeaders(array $data): void
    {
        $fileHeaders = array_keys($data);
        $missingHeaders = array_diff($this->expectedHeaders, $fileHeaders);
        $extraHeaders = array_diff($fileHeaders, $this->expectedHeaders);

        if (!empty($missingHeaders) || !empty($extraHeaders)) {
            throw new Exception("Headers in the file don't match the required ones!");
        }
    }

    /**
     * Log changes or failures to the database.
     *
     * @param int $rowNumber
     * @param string $column
     * @param mixed $oldValue
     * @param mixed $newValue
     * @param string|null $errorMessage
     */
    protected function logImport(int $rowNumber, string $column, $oldValue, $newValue, ?string $errorMessage = null): void
    {
        ImportLog::create([
            'import_id' => $this->importId,
            'row_number' => $rowNumber,
            'column' => $column,
            'old_value' => json_encode($oldValue),
            'new_value' => json_encode($newValue),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Convert Excel date to PHP DateTime object.
     *
     * @param mixed $dateValue
     * @return \DateTime|null
     */
    protected function convertExcelDate($dateValue): ?\DateTime
    {
        return $dateValue ? Date::excelToDateTimeObject($dateValue) : null;
    }

    abstract public function model(array $row);
}
