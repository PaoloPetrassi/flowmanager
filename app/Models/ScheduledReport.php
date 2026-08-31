<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    protected $fillable = ['user_id', 'name', 'report_type', 'frequency', 'email', 'is_active', 'last_sent_at', 'next_run_at', 'last_error'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_sent_at' => 'datetime', 'next_run_at' => 'datetime'];
    }
}
