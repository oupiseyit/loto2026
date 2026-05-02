<?php

namespace App\Virtual\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReportSummary',
    properties: [
        new OA\Property(property: 'total_bets',   type: 'integer', example: 120),
        new OA\Property(property: 'total_amount', type: 'number',  format: 'float', example: 450000),
        new OA\Property(property: 'total_win',    type: 'number',  format: 'float', example: 80000),
        new OA\Property(property: 'net',          type: 'number',  format: 'float', example: 370000),
        new OA\Property(property: 'by_session',   type: 'object'),
    ]
)]
#[OA\Schema(
    schema: 'ReportDaily',
    properties: [
        new OA\Property(property: 'date',   type: 'string',  format: 'date', example: '2026-05-01'),
        new OA\Property(property: 'bets',   type: 'integer', example: 40),
        new OA\Property(property: 'amount', type: 'number',  format: 'float', example: 150000),
        new OA\Property(property: 'win',    type: 'number',  format: 'float', example: 20000),
    ]
)]
#[OA\Schema(
    schema: 'ReportStaff',
    properties: [
        new OA\Property(property: 'id',     type: 'integer', example: 3),
        new OA\Property(property: 'name',   type: 'string',  example: 'បង ម៉េន'),
        new OA\Property(property: 'bets',   type: 'integer', example: 40),
        new OA\Property(property: 'amount', type: 'number',  format: 'float', example: 150000),
        new OA\Property(property: 'win',    type: 'number',  format: 'float', example: 20000),
        new OA\Property(property: 'net',    type: 'number',  format: 'float', example: 130000),
    ]
)]
class ReportSchema {}
