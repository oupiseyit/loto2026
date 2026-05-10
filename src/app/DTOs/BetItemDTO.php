<?php

namespace App\DTOs;

readonly class BetItemDTO
{
    public function __construct(
        public string $betType,
        public string $letter,
        public string $position,
        public string $number,
        public float  $amount,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self($data['bet_type'], $data['letter'], $data['position'], $data['number'], (float) $data['amount']);
    }

    public function toModelArray(int $ticketId, int $userId): array
    {
        return [
            'ticket_id' => $ticketId,
            'user_id'   => $userId,
            'bet_type'  => $this->betType,
            'letter'    => $this->letter,
            'position'  => $this->position,
            'number'    => $this->number,
            'amount'    => $this->amount,
        ];
    }
}
