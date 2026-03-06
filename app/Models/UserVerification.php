<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model{
    use HasFactory;
    protected $table = "user_verifications"; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'user_type',
        'otp',
        'verfication_otp_time',
    ];
}
