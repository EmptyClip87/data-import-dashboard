<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $fillable = [
        'user_id', 'import_type', 'file', 'original_file_name', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function import_logs()
    {
        return $this->hasMany(ImportLog::class);
    }
    public function audits()
    {
        return $this->hasMany(Audit::class);
    }
}
