<?php

// Screen: record | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'invoice_no' => $this->invoice_number,
            'user_name'  => $this->whenLoaded('user', fn () => $this->user->name),
            'session'    => $this->session,
            'bet_date'   => $this->bet_date?->toDateString(),
            'quantity'   => $this->bets_count ?? $this->bets()->count(),
            'bet_amount' => (float) $this->total_amount,
            'win_amount' => (float) $this->win_amount,
            'status'     => $this->status,
        ];
    }
}
