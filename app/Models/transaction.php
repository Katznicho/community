<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'amount',
        'type',
        'status',
        'description',
        'reference',
        'network_code',
        'service_code',
        'community_id',
        'user_id',
    ];

    //a transaction belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
