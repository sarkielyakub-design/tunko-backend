<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->cards
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'card_holder' => 'required',
            'card_number' => 'required',
            'brand' => 'required',
            'expiry_month' => 'required',
            'expiry_year' => 'required',
        ]);

        $card = Card::create([
            'user_id' => $request->user()->id,
            'card_holder' => $request->card_holder,
            'card_number' => $request->card_number,
            'last_four' => substr($request->card_number, -4),
            'brand' => $request->brand,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
        ]);

        return response()->json([
            'success' => true,
            'data' => $card
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $card = Card::where(
            'user_id',
            $request->user()->id
        )->findOrFail($id);

        $card->delete();

        return response()->json([
            'success' => true,
            'message' => 'Card deleted.'
        ]);
    }

    public function setDefault(Request $request, $id)
    {
        Card::where(
            'user_id',
            $request->user()->id
        )->update([
            'is_default' => false
        ]);

        $card = Card::where(
            'user_id',
            $request->user()->id
        )->findOrFail($id);

        $card->update([
            'is_default' => true
        ]);

        return response()->json([
            'success' => true,
            'data' => $card
        ]);
    }

    public function freeze(Request $request, $id)
    {
        $card = Card::where(
            'user_id',
            $request->user()->id
        )->findOrFail($id);

        $card->update([
            'is_frozen' => !$card->is_frozen
        ]);

        return response()->json([
            'success' => true,
            'data' => $card
        ]);
    }
}