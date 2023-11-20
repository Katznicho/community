<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UssdSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'phone_number',
        'text',
        'network_code',
        'service_code',
        'last_user_code',
    ];
}
