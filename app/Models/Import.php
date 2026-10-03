<?php

namespace App\Models;

use App\ImportStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends Model
{
    protected $fillable = [
        'file_name',
        'file_size',
        'total_records',
        'processed_records',
        'total_success',
        'total_faild',
        'status',
    ];

    public function casts()
    {
        return ['status' => ImportStatusEnum::class];
    }

    public function results(): HasMany
    {
        return $this->hasMany(ImportResult::class, 'import_id');
    }
}
