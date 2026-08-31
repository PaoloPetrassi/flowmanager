<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRun extends Model
{
    protected $fillable = ['user_id', 'resource_type', 'original_filename', 'total_rows', 'imported_rows', 'failed_rows', 'errors'];

    protected function casts(): array
    {
        return ['errors' => 'array'];
    }
}
