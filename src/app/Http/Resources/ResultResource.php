<?php

// Screen: result | Theme: gold/crimson | Stack: Laravel+Inertia+React+API+Docker+MySQL

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'result_date' => $this->result_date?->toDateString(),
            'session'     => $this->session,
            'position'    => $this->position,
            'number'      => $this->number,
            'entered_by'  => $this->whenLoaded('enteredBy', fn () => $this->enteredBy->name),
        ];
    }
}
