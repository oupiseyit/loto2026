<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;
    protected $fillable = [
        'ticket_id', 'user_id', 'bet_type', 'letter',
        'position', 'number', 'amount', 'is_winner', 'win_amount',
    ];

    protected $casts = ['is_winner' => 'boolean'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
