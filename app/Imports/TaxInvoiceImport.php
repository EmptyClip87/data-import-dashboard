<?php

namespace App\Imports;

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

class TaxInvoiceImport extends BaseImport implements
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
        parent::__construct('invoices.files.tax_invoice');
    }

    /**
     * @param array $row
     * @return TaxInvoice|null
     */
    public function model(array $row)
    {
        $invoiceDate = $this->convertExcelDate($row['invoice_date']);
        $existingInvoice = TaxInvoice::where('invoice_number', $row['invoice_number'])
            ->where('gst_id', $row['gst_id'])
            ->first();

        if ($existingInvoice) {
            foreach ($row as $column => $newValue) {
                $oldValue = $existingInvoice->{$column};
                if ($column === 'invoice_date') {
                    $newValue = $invoiceDate->format('Y-m-d');
                }
                if ($oldValue != $newValue) {
                    $this->logImport($existingInvoice, $this->getRowNumber(), $column, $newValue, $oldValue, $newValue);
                }
            }

            $existingInvoice->update($this->prepareInvoiceData($row, $invoiceDate));
            return null; // Return null to skip creating a new model
        }

        return new TaxInvoice($this->prepareInvoiceData($row, $invoiceDate));
    }

    private function prepareInvoiceData(array $row, \DateTime $date): array
    {
        return [
            'invoice_date' => $date->format('Y-m-d'),
            'invoice_number' => $row['invoice_number'],
            'gst_id' => $row['gst_id'],
            'action_id' => $row['action_id'],
            'amount' => $row['amount'],
            'deduction' => $row['deduction'],
            'total' => $row['total'],
        ];
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
     *  Validating headers
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function prepareForValidation(array $data): array
    {
        $this->validateHeaders($data);
        return $data;
    }

    public function onFailure(...$failures)
    {
        foreach ($failures as $failure) {
            $newValue = json_encode($failure->values()[$failure->attribute()]);
            $errorMessages = implode(', ', $failure->errors());
            $this->logAudit($failure->row(), $failure->attribute(), $newValue, $errorMessages);
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
