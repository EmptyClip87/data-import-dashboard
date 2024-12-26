<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BasicInvoice extends Model
{
    protected $fillable = [
        'invoice_date',
        'due_date',
        'invoice_number',
        'po',
        'item',
        'payment_method',
        'price',
        'tax',
        'total_price'
    ];

    public function logs()
    {
        return $this->morphMany(ImportLog::class, 'row');
    }
}
