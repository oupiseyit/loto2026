<?php

namespace App\Virtual\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Setting',
    properties: [
        new OA\Property(property: 'bluetooth',  type: 'boolean', example: true),
        new OA\Property(property: 'device',     type: 'string',  enum: ['58mm', '80mm']),
        new OA\Property(property: 'logo',       type: 'string',  enum: ['logo', 'text']),
        new OA\Property(property: 'commission', type: 'string',  enum: ['default', 'custom']),
    ]
)]
class SettingSchema {}
