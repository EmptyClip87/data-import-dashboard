<?php

namespace App\Imports;

use App\BasicInvoice;
use Exception;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BasicInvoiceImport extends BaseImport implements
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
        parent::__construct('invoices.files.basic_invoice');
    }

    /**
     * @param array $row
     * @return BasicInvoice|null
     */
    public function model(array $row)
    {
        $invoiceDate = $this->convertExcelDate($row['invoice_date']);
        $dueDate = $this->convertExcelDate($row['due_date']);
        $existingInvoice = BasicInvoice::where('invoice_number', $row['invoice_number'])
            ->where('po', $row['po'])
            ->first();

        if ($existingInvoice) {
            foreach ($row as $column => $newValue) {
                $oldValue = $existingInvoice->{$column};
                if ($column === 'invoice_date') {
                    $newValue = $invoiceDate->format('Y-m-d');
                } elseif ($column === 'due_date') {
                    $newValue = $dueDate->format('Y-m-d');
                }
                if ($oldValue != $newValue) {
                    $this->logImport($existingInvoice, $this->getRowNumber(), $column, $newValue, $oldValue, $newValue);
                }
            }

            $existingInvoice->update($this->prepareInvoiceData($row, $invoiceDate, $dueDate));
            return null; // Return null to skip creating a new model
        }

        return new BasicInvoice($this->prepareInvoiceData($row, $invoiceDate, $dueDate));
    }

    private function prepareInvoiceData(array $row, \DateTime $invoiceDate, \DateTime $dueDate): array
    {
        return [
            'invoice_date' => $invoiceDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'invoice_number' => $row['invoice_number'],
            'po' => $row['po'],
            'item' => $row['item'],
            'payment_method' => $row['payment_method'],
            'price' => $row['price'],
            'tax' => $row['tax'],
            'total_price' => $row['total'],
        ];
    }

    public function rules(): array
    {
        return [
            'invoice_date' => 'required',
            'due_date' => 'required',
            'invoice_number' => 'required|string',
            'po' => 'required|string',
            'item' => 'required|string',
            'payment_method' => 'required|string',
            'price' => 'required|numeric',
            'tax' => 'required|numeric',
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
    public function prepareForValidation(array $data)
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
