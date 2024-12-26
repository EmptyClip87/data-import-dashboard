<?php

namespace App\Imports;

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

class StandardOrderImport extends BaseImport implements
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
        parent::__construct('orders.files.standard_order');
    }

    /**
     * @param array $row
     * @return StandardOrder|null
     */
    public function model(array $row): ?StandardOrder
    {
        $date = $this->convertExcelDate($row['order_date']);
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
                    $this->logImport($this->getRowNumber(), $column, $oldValue, $newValue);
                }
            }

            $existingOrder->update($this->prepareOrderData($row, $date));
            return null; // Return null to skip creating a new model
        }

        return new StandardOrder($this->prepareOrderData($row, $date));
    }

    private function prepareOrderData(array $row, \DateTime $date): array
    {
        return [
            'order_date' => $date->format('Y-m-d'),
            'channel' => $row['channel'],
            'sku' => $row['sku'],
            'item_description' => $row['item_description'] ?? null,
            'origin' => $row['origin'],
            'so_num' => $row['so'],
            'cost' => $row['cost'],
            'shipping_cost' => $row['shipping_cost'],
            'total_price' => $row['total_price'],
        ];
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

    /**
     *  Validating headers
     *
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
            $this->logImport($failure->row(), $failure->attribute(), null, $newValue, $errorMessages);
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
