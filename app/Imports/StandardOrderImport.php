<?php

namespace App\Imports;

use App\ImportLog;
use App\StandardOrder;
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

class StandardOrderImport implements
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
        $this->expectedHeaders = array_keys(config('import_types.orders.files.standard_order.headers_to_db'));
    }

    /**
     * @param array $row
     * @return StandardOrder|null
     */
    public function model(array $row)
    {
        $date = Date::excelToDateTimeObject($row['order_date']);
        $importId = session('current_import_id');

        $existingOrder = StandardOrder::where('so_num', $row['so'])
            ->where('sku', $row['sku'])
            ->first();
        if ($existingOrder) {
            foreach ($row as $column => $newValue) {
                $oldValue = $existingOrder->{$column};
                if ($column === 'order_date') {
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

            // Update existing order
            $existingOrder->update([
                'order_date' => $date->format('Y-m-d'),
                'channel' => $row['channel'],
                'sku' => $row['sku'],
                'item_description' => $row['item_description'] ?? null,
                'origin' => $row['origin'],
                'so_num' => $row['so'],
                'cost' => $row['cost'],
                'shipping_cost' => $row['shipping_cost'],
                'total_price' => $row['total_price'],
            ]);

            return null; // Return null to skip creating a new model
        }

        // Create a new StandardOrder
        return new StandardOrder([
            'order_date' => $date->format('Y-m-d'),
            'channel' => $row['channel'],
            'sku' => $row['sku'],
            'item_description' => $row['item_description'] ?? null,
            'origin' => $row['origin'],
            'so_num' => $row['so'],
            'cost' => $row['cost'],
            'shipping_cost' => $row['shipping_cost'],
            'total_price' => $row['total_price'],
        ]);
    }

    public function rules(): array
    {
        return [
            'order_date' => 'required',
            'channel' => 'required|string|in:PT,Amazon,eBay',
            'sku' => 'required|string',
            'item_description' => 'nullable|string',
            'origin' => 'required|string',
            'so' => 'required|string',
            'cost' => 'required|numeric',
            'shipping_cost' => 'required|numeric',
            'total_price' => 'required|numeric',
        ];
    }

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
