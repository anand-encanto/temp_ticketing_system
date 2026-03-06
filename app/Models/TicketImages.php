<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TicketImages extends Model
{
    protected $table = "ticket_images";
    protected $fillable = ['ticket_id', 'image_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Tickets::class);
    }
}
