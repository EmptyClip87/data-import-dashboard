<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = [
        'import_id', 'row_number', 'column', 'error_value', 'error_message'
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }
}
