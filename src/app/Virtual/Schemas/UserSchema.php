<?php

namespace App\Virtual\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id',       type: 'integer', example: 1),
        new OA\Property(property: 'name',     type: 'string',  example: 'បង ម៉េន'),
        new OA\Property(property: 'username', type: 'string',  example: 'staff01'),
        new OA\Property(property: 'role',     type: 'string',  enum: ['admin', 'master', 'staff']),
    ]
)]
class UserSchema {}
