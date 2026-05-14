<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaLevel extends Model
{
    use HasFactory;

    protected $table = 'sla_levels';

    protected $fillable = [
        'priority',
        'response_time_minutes',
        'resolution_time_minutes',
        'reminder_interval_minutes',
    ];
}
