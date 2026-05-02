<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'HT Lotto API',
    version: '1.0.0',
    description: 'REST API for HT ភ្នាក់ Lottery Agent App. Roles: admin | master | staff.',
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'sanctum',
)]
#[OA\Tag(name: 'Auth',     description: 'Authentication')]
#[OA\Tag(name: 'Bets',     description: 'Betting — all roles')]
#[OA\Tag(name: 'Records',  description: 'Bet records — all roles')]
#[OA\Tag(name: 'Results',  description: 'Lottery results')]
#[OA\Tag(name: 'Reports',  description: 'Reports — admin and master only')]
#[OA\Tag(name: 'Settings', description: 'Printer and commission settings')]
#[OA\Tag(name: 'Account',  description: 'Account and sales')]
abstract class Controller {}
