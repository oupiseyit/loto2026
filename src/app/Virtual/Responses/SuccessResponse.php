<?php

namespace App\Virtual\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SuccessResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string',  example: 'OK'),
    ]
)]
class SuccessResponse {}
