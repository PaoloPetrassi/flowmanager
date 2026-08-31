<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedFilter extends Model
{
    protected $fillable = ['user_id', 'resource_type', 'name', 'filters', 'is_default'];

    protected function casts(): array
    {
        return ['filters' => 'array', 'is_default' => 'boolean'];
    }
}
