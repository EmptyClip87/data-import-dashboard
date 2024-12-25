<?php

namespace App\Imports;

use App\BasicInvoice;

class BasicInvoiceImport extends BaseImport
{
    public function __construct()
    {
        parent::__construct('invoices.files.basic_invoice');
    }

    public function model(array $row)
    {
        $invoiceDate = $this->convertExcelDate($row['invoice_date']);
        $dueDate = $this->convertExcelDate($row['due_date']);
        $existingInvoice = BasicInvoice::where('invoice_number', $row['invoice_number'])
            ->where('po_num', $row['po'])
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
                    $this->logImport($this->getRowNumber(), $column, $oldValue, $newValue);
                }
            }

            $existingInvoice->update($this->prepareInvoiceData($row, $invoiceDate, $dueDate));
            return null;
        }

        return new BasicInvoice($this->prepareInvoiceData($row, $invoiceDate, $dueDate));
    }

    private function prepareInvoiceData(array $row, \DateTime $invoiceDate, \DateTime $dueDate): array
    {
        return [
            'invoice_date' => $invoiceDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'invoice_number' => $row['invoice_number'],
            'po_num' => $row['po'],
            'item' => $row['item'],
            'payment_method' => $row['payment_method'],
            'price' => $row['price'],
            'tax' => $row['tax'],
            'total_price' => $row['total'],
        ];
    }
}
