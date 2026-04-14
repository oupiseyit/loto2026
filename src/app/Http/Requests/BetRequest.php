<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session'           => ['required', 'in:morning,noon,evening'],
            'bet_date'          => ['required', 'date'],
            'bets'              => ['required', 'array', 'min:1'],
            'bets.*.bet_type'   => ['required', 'in:ABCD,LO'],
            'bets.*.letter'     => ['required', 'string', 'max:10'],
            'bets.*.position'   => ['required', 'string', 'max:5'],
            'bets.*.number'     => ['required', 'string', 'max:10'],
            'bets.*.amount'     => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
