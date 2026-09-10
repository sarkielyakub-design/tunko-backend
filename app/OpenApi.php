<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Tunko Money API",
    description: "Tunko Money Backend REST API"
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "Production Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class OpenApi
{
}