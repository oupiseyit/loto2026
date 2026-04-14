<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'bluetooth_enabled', 'printer_size',
        'logo_mode', 'commission_mode', 'commission_value',
    ];

    protected $casts = ['bluetooth_enabled' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
