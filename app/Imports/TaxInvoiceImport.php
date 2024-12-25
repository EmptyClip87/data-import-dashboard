<?php

namespace App\Imports;

use App\TaxInvoice;

class TaxInvoiceImport extends BaseImport
{
    public function __construct()
    {
        parent::__construct('invoices.files.tax_invoice');
    }

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
                    $this->logImport($this->getRowNumber(), $column, $oldValue, $newValue);
                }
            }

            $existingInvoice->update($this->prepareInvoiceData($row, $invoiceDate));
            return null;
        }

        return new TaxInvoice($this->prepareInvoiceData($row, $invoiceDate));
    }

    private function prepareInvoiceData(array $row, \DateTime $invoiceDate): array
    {
        return [
            'invoice_date' => $invoiceDate->format('Y-m-d'),
            'invoice_number' => $row['invoice_number'],
            'gst_id' => $row['gst_id'],
            'action_id' => $row['action_id'],
            'amount' => $row['amount'],
            'deduction' => $row['deduction'],
            'total' => $row['total'],
        ];
    }
}
