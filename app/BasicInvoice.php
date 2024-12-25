<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BasicInvoice extends Model
{
    protected $fillable = [
        'invoice_date',
        'due_date',
        'invoice_number',
        'po_num',
        'item',
        'payment_method',
        'price',
        'tax',
        'total_price'
    ];
}
