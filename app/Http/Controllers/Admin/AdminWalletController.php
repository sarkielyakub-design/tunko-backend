<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;

class AdminWalletController extends AdminController
{
    /**
     * Display admin wallet.
     */
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin wallet endpoint.',
            'data' => [],
        ]);
    }

    /**
     * Wallet transactions.
     */
    public function transactions(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin wallet transactions.',
            'data' => [],
        ]);
    }

    /**
     * Fund admin wallet.
     */
    public function fund(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet funded successfully.',
            'data' => $validated,
        ]);
    }

    /**
     * Update wallet.
     */
    public function update(Request $request, $adminWallet)
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin wallet updated.',
            'wallet_id' => $adminWallet,
        ]);
    }
}