<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\Bonus;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function addBonus(Request $request, $investmentId)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:one-time,weekly',
            'expire_date'=>'required|date',
        ]);

        $investment = Investment::findOrFail($investmentId);
     
        // Create and save the bonus record
        $bonus = $investment->bonuses()->create([
            'amount' => $request->amount,
            'type' => $request->type,
            'bonus_date' => now(),
            'expire_date'=>$request->expire_date
        ]);
       
        // Return success response
        return response()->json(['message' => 'Bonus added successfully', 'bonus' => $bonus]);
    }



}
