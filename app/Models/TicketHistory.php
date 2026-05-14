<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    use HasFactory;

    protected $table = 'ticket_histories';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'status_from',
        'status_to',
        'assignment_from',
        'assignment_to',
        'priority_from',
        'priority_to',
        'change_type',
        'message',
    ];

    public function ticket()
    {
        return $this->belongsTo(Tickets::class, 'ticket_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function from_assignee()
    {
        return $this->belongsTo(User::class, 'assignment_from', 'id');
    }

    public function to_assignee()
    {
        return $this->belongsTo(User::class, 'assignment_to', 'id');
    }
}
