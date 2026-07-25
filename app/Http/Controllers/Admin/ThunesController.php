<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ThunesController extends Controller
{
    public function health()
    {
        return response()->json([
            'success' => true,
            'provider' => 'Thunes',
            'status' => 'connected',
        ]);
    }
}