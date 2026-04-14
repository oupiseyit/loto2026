<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    protected $fillable = [
        'result_date', 'session', 'position', 'number', 'entered_by',
    ];

    protected $casts = ['result_date' => 'date'];

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
