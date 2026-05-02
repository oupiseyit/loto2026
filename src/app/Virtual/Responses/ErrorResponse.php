<?php

namespace App\Virtual\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string',  example: 'Unauthorized'),
        new OA\Property(property: 'errors',  type: 'object'),
    ]
)]
class ErrorResponse {}
