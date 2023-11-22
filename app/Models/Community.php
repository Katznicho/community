<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'account_name',
        'account_number',
        'account_balance',
        'leader_id'
    ];

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    //community has many transactions
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
