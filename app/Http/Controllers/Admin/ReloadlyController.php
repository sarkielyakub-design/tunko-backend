<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reloadly\ReloadlyClient;
use Throwable;

class ReloadlyController extends Controller
{
    public function __construct(
        private readonly ReloadlyClient $client
    ) {}

    public function health()
    {
        try {

            $response = $this->client->countries();

            return response()->json([
                "success" => true,
                "provider" => "Reloadly",
                "status" => "connected",
                "http_status" => $response->status(),
                "records" => count($response->json() ?? []),
            ]);

        } catch (Throwable $e) {

            return response()->json([
                "success" => false,
                "provider" => "Reloadly",
                "status" => "failed",
                "message" => $e->getMessage(),
            ], 500);

        }
    }
}