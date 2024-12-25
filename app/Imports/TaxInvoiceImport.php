<?php

namespace App\Imports;

use App\ImportLog;
use App\TaxInvoice;
use Exception;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TaxInvoiceImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use SkipsFailures;
    use RemembersRowNumber;

    protected $expectedHeaders;

    public function __construct()
    {
        $this->expectedHeaders = array_keys(config('import_types.invoices.files.tax_invoice.headers_to_db'));
    }

    public function model(array $row)
    {
        $date = Date::excelToDateTimeObject($row['invoice_date']);
        $importId = session('current_import_id');

        $existingInvoice = TaxInvoice::where('invoice_number', $row['invoice_number'])
            ->where('gst_id', $row['gst_id'])
            ->first();
        if ($existingInvoice) {
            foreach ($row as $column => $newValue) {
                $oldValue = $existingInvoice->{$column};
                if ($column === 'invoice_date') {
                    $newValue = $date->format('Y-m-d');
                }
                if ($oldValue != $newValue) {
                    ImportLog::create([
                        'import_id' => $importId,
                        'row_number' => $this->getRowNumber(),
                        'column' => $column,
                        'old_value' => json_encode($oldValue),
                        'new_value' => json_encode($newValue),
                        'error_message' => null, // No error, just changes
                    ]);
                }
            }

            $existingInvoice->update([
                'invoice_date' => $date->format('Y-m-d'),
                'invoice_number' => $row['invoice_number'],
                'gst_id' => $row['gst_id'],
                'action_id' => $row['action_id'],
                'amount' => $row['amount'],
                'deduction' => $row['deduction'],
                'total' => $row['total'],
            ]);

            return null;
        }

        return new TaxInvoice([
            'invoice_date' => $date->format('Y-m-d'),
            'invoice_number' => $row['invoice_number'],
            'gst_id' => $row['gst_id'],
            'action_id' => $row['action_id'],
            'amount' => $row['amount'],
            'deduction' => $row['deduction'],
            'total' => $row['total'],
        ]);
    }

    public function rules(): array
    {
        return [
            'invoice_date' => 'required',
            'invoice_number' => 'required|string',
            'gst_id' => 'required|string',
            'action_id' => 'required|numeric',
            'amount' => 'required|numeric',
            'deduction' => 'required|numeric',
            'total' => 'required|numeric',
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function prepareForValidation(array $data)
    {
        $fileHeaders = array_keys($data);
        $missingHeaders = array_diff($this->expectedHeaders, $fileHeaders);
        $extraHeaders = array_diff($fileHeaders, $this->expectedHeaders);
        if (!empty($missingHeaders) || !empty($extraHeaders)) {
            throw new Exception("Headers in the file don't match the required ones!");
        }
        return $data;
    }

    public function onFailure(...$failures)
    {
        foreach ($failures as $failure) {
            ImportLog::create([
                'import_id' => session('current_import_id'),
                'row_number' => $failure->row(),
                'column' => $failure->attribute(),
                'old_value' => null,
                'new_value' => json_encode($failure->values()[$failure->attribute()]),
                'error_message' => implode(', ', $failure->errors()),
            ]);
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
