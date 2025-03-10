<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'import_id', 'row_id', 'row_type', 'column', 'old_value', 'new_value'
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function row()
    {
        return $this->morphTo();
    }
}
