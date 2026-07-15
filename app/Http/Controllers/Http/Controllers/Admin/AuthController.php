<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\Admin\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $service
    ) {
    }

    public function login(
        LoginRequest $request
    ) {

        $response = $this->service->login(
            $request->email,
            $request->password
        );

        if (!$response['success']) {

            return response()->json([

                'success' => false,

                'message' => $response['message'],

            ],401);

        }

        return response()->json([

            'success' => true,

            'token' => $response['token'],

            'admin' => new AdminResource(
                $response['admin']
            ),

        ]);
    }

    public function profile(
        Request $request
    ) {

        return response()->json([

            'success' => true,

            'data' => new AdminResource(
                $request->user()
            ),

        ]);

    }

    public function logout(
        Request $request
    ) {

        $request
            ->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([

            'success' => true,

            'message' => 'Logged out successfully.',

        ]);

    }
}