<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API for importing, searching, and reserving accommodation offers.',
    title: 'WTG API',
)]
#[OA\Server(
    url: '/',
    description: 'Current application server',
)]

final class Documentation {}
