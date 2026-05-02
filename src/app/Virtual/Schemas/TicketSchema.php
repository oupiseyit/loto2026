<?php

namespace App\Virtual\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Ticket',
    properties: [
        new OA\Property(property: 'id',             type: 'integer', example: 1),
        new OA\Property(property: 'invoice_number', type: 'string',  example: 'INV-20260502-0001'),
        new OA\Property(property: 'session',        type: 'string',  enum: ['morning', 'noon', 'evening']),
        new OA\Property(property: 'bet_date',       type: 'string',  format: 'date', example: '2026-05-02'),
        new OA\Property(property: 'total_amount',   type: 'number',  format: 'float', example: 15000),
        new OA\Property(property: 'win_amount',     type: 'number',  format: 'float', example: 0),
        new OA\Property(property: 'status',         type: 'string',  enum: ['draft', 'pending', 'settled']),
        new OA\Property(property: 'bets_count',     type: 'integer', example: 3),
    ]
)]
class TicketSchema {}
