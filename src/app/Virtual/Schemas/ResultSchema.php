<?php

namespace App\Virtual\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ResultItem',
    properties: [
        new OA\Property(property: 'position', type: 'string', example: 'A'),
        new OA\Property(property: 'number',   type: 'string', example: '25'),
    ]
)]
class ResultSchema {}
