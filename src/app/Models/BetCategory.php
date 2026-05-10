<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BetCategory extends Model
{
    protected $fillable = ['name', 'type', 'status', 'sort_order'];

    protected $casts = ['status' => 'boolean'];

    public const TYPE_P = 1;

    public const TYPE_LO = 2;

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeP(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_P);
    }

    public function scopeLo(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_LO);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_LO ? 'LO' : 'P';
    }
}
