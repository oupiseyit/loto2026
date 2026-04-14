<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'game_code',
        'bet_letter',
        'win_number',
        'invoice_no',
        'position_flag',
        'payout',
    ];

    protected $casts = [
        'payout' => 'decimal:2',
    ];

    /**
     * Only rows that have an actual payout entry.
     */
    public function scopeFilled($query)
    {
        return $query->whereNotNull('bet_letter');
    }

    /**
     * Only winning rows (X-type position).
     */
    public function scopeXType($query)
    {
        return $query->where('position_flag', 'x');
    }
}
