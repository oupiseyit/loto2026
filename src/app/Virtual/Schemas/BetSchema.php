<?php

namespace App\Virtual\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Bet',
    properties: [
        new OA\Property(property: 'id',       type: 'integer', example: 1),
        new OA\Property(property: 'bet_type', type: 'string',  enum: ['ABCD', 'LO']),
        new OA\Property(property: 'letter',   type: 'string',  enum: ['A', 'B', 'C', 'D', 'F', 'I', 'N']),
        new OA\Property(property: 'position', type: 'string',  enum: ['X', 'W', 'H', 'W*']),
        new OA\Property(property: 'number',   type: 'string',  example: '25'),
        new OA\Property(property: 'amount',   type: 'number',  format: 'float', example: 5000),
    ]
)]
class BetSchema {}
