<?php

namespace App\Models;

use App\ImportResultStatusEnum;
use Illuminate\Database\Eloquent\Model;

class ImportResult extends Model
{
    protected $table = 'import_results';

    protected $fillable = [
        'status',
        'row',
        'values',
        'errors',
    ];

    public function casts()
    {
        return [
            'values' => 'array',
            'errors' => 'array',
            'status' => ImportResultStatusEnum::class,
        ];
    }
}
