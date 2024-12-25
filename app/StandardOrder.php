<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StandardOrder extends Model
{
    protected $fillable = [
        'order_date',
        'channel',
        'sku',
        'item_description',
        'origin',
        'so_num',
        'cost',
        'shipping_cost',
        'total_price'
    ];
}
