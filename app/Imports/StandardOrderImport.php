<?php

namespace App\Imports;

use App\StandardOrder;

class StandardOrderImport extends BaseImport
{
    public function __construct()
    {
        parent::__construct('orders.files.standard_order');
    }

    public function model(array $row)
    {
        $orderDate = $this->convertExcelDate($row['order_date']);
        $existingOrder = StandardOrder::where('so_num', $row['so'])
            ->where('sku', $row['sku'])
            ->first();

        if ($existingOrder) {
            foreach ($row as $column => $newValue) {
                $oldValue = $existingOrder->{$column};
                if ($column === 'order_date') {
                    $newValue = $orderDate->format('Y-m-d');
                }
                if ($oldValue != $newValue) {
                    $this->logImport($this->getRowNumber(), $column, $oldValue, $newValue);
                }
            }

            $existingOrder->update($this->prepareOrderData($row, $orderDate));
            return null;
        }

        return new StandardOrder($this->prepareOrderData($row, $orderDate));
    }

    private function prepareOrderData(array $row, \DateTime $orderDate): array
    {
        return [
            'order_date' => $orderDate->format('Y-m-d'),
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
}
