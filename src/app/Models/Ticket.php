<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'session', 'bet_date', 'total_amount',
        'invoice_number', 'status', 'win_amount',
    ];

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['pending', 'won', 'lost']);
    }

    protected $casts = ['bet_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
