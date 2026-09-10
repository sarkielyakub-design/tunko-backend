<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ExchangeRate::all()
        ]);
    }
}