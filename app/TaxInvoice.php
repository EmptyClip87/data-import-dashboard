<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxInvoice extends Model
{
    protected $fillable = [
        'invoice_date',
        'invoice_number',
        'gst_id',
        'action_id',
        'amount',
        'deduction',
        'total'
    ];
}
