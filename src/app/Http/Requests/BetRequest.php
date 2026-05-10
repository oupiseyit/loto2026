<?php

namespace App\Http\Requests;

use App\Models\BetCategory;
use App\Models\BetTimeSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session'         => ['required', 'string', Rule::in(BetTimeSetting::active()->pluck('session_key')->all())],
            'bet_date'        => ['required', 'date'],
            'bets'            => ['required', 'array', 'min:1'],
            'bets.*.bet_type' => ['required', Rule::in(BetCategory::active()->pluck('name')->all())],
            'bets.*.letter'   => ['required', 'string', 'max:10'],
            'bets.*.position' => ['required', 'string', 'max:5'],
            'bets.*.number'   => ['required', 'string', 'max:10'],
            'bets.*.amount'   => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
