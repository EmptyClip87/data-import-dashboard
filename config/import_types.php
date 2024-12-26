<?php

return [
    'orders' => [
        'label' => 'Import Orders',
        'permission_required' => 'import-orders',
        'files' => [
            'standard_order' => [
                'label' => 'Standard',
                'headers_to_db' => [
                    'order_date' => [
                        'label' => 'Order Date',
                        'type' => 'date',
                        'validation' => ['required'],
                    ],
                    'channel' => [
                        'label' => 'Channel',
                        'type' => 'string',
                        'validation' => ['required', 'in' => ['PT', 'Amazon', 'eBay']],
                    ],
                    'sku' => [
                        'label' => 'SKU',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'item_description' => [
                        'label' => 'Item Description',
                        'type' => 'string',
                        'validation' => ['nullable'],
                    ],
                    'origin' => [
                        'label' => 'Origin',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'so' => [
                        'label' => 'SO#',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'cost' => [
                        'label' => 'Cost',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                    'shipping_cost' => [
                        'label' => 'Shipping Cost',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                    'total_price' => [
                        'label' => 'Total Price',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                ],
                'update_or_create' => ['so_num', 'sku'],
            ],
        ],
    ],
    'invoices' => [
        'label' => 'Import Invoices',
        'permission_required' => 'import-invoices',
        'files' => [
            'basic_invoice' => [
                'label' => 'Basic Invoice',
                'headers_to_db' => [
                    'invoice_date' => [
                        'label' => 'Invoice Date',
                        'type' => 'date',
                        'validation' => ['required'],
                    ],
                    'due_date' => [
                        'label' => 'Due Date',
                        'type' => 'date',
                        'validation' => ['required'],
                    ],
                    'invoice_number' => [
                        'label' => 'Invoice Number',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'po' => [
                        'label' => 'PO#',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'item' => [
                        'label' => 'Item',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'payment_method' => [
                        'label' => 'Payment Method',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'price' => [
                        'label' => 'Price',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                    'tax' => [
                        'label' => 'Tax',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                    'total' => [
                        'label' => 'Total Price',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                ],
                'update_or_create' => ['invoice_number', 'po_num'],
            ],
            'tax_invoice' => [
                'label' => 'Tax Invoice',
                'headers_to_db' => [
                    'invoice_date' => [
                        'label' => 'Invoice Date',
                        'type' => 'date',
                        'validation' => ['required'],
                    ],
                    'invoice_number' => [
                        'label' => 'Invoice Number',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'gst_id' => [
                        'label' => 'GST ID',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'action_id' => [
                        'label' => 'Action ID',
                        'type' => 'string',
                        'validation' => ['required'],
                    ],
                    'amount' => [
                        'label' => 'Amount',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                    'deduction' => [
                        'label' => 'Deduction',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                    'total' => [
                        'label' => 'Total',
                        'type' => 'double',
                        'validation' => ['required'],
                    ],
                ],
                'update_or_create' => ['invoice_number', 'gst_id'],
            ],
        ],
    ],
];
