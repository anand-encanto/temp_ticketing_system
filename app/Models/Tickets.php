<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    use HasFactory;
    
    protected $table = "tickets"; // Corrected property


    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Locations::class, 'location_id', 'id');
    }

    public function submit_by()
    {
        return $this->belongsTo(User::class, 'submitter_id', 'id');
    }


    public function assign_to()
    {
        return $this->belongsTo(User::class, 'assignee_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(TicketImages::class, 'ticket_id', 'id');
    }

    public function comment()
    {
        return $this->hasMany(Comment::class, 'ticket_id', 'id');
    }

}
