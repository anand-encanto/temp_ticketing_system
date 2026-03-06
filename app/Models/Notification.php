<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
        
    protected $fillable = ['ticket_id', 'user_id', 'recipient_id','trigger_event','title','message','status'];    

    protected $table = "notification"; // Corrected property
}
