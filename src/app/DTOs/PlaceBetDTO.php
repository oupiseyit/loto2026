<?php

namespace App\DTOs;

use App\Http\Requests\BetRequest;

readonly class PlaceBetDTO
{
    /** @param BetItemDTO[] $bets */
    public function __construct(
        public string $session,
        public string $betDate,
        public array  $bets,
    ) {}

    public static function fromRequest(BetRequest $request): self
    {
        return new self(
            session: $request->validated('session'),
            betDate: $request->validated('bet_date'),
            bets:    array_map(
                fn (array $b) => BetItemDTO::fromArray($b),
                $request->validated('bets'),
            ),
        );
    }

    public function totalAmount(): float
    {
        return (float) array_sum(array_map(fn (BetItemDTO $b) => $b->amount, $this->bets));
    }
}
