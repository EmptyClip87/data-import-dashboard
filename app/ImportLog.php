<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'import_id', 'row_number', 'column', 'new_value', 'old_value', 'old_value', 'error_message'
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }
}
