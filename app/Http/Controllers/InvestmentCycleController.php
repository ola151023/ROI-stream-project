<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class InvestmentCycleController extends Controller
{



    public function index(){
        return InvestmentCycle::all();
    }

    public function creatingInvestmentCycle($investmentId, $cycle_days)
    {
        try {
            // Create the investment cycle
            $investmentCycle = InvestmentCycle::create([
                'investment_id' => $investmentId,
                'cycle_start_date' => now(), // Assuming cycle starts immediately after investment
                'cycle_end_date' => now()->addDays($cycle_days),
            ]);

            return $investmentCycle; // Return the created investment cycle
        } catch (\Exception $e) {
            // Handle any exceptions
            // Log the error
            Log::error('Error creating investment cycle: ' . $e->getMessage());

            // You can return null or throw an exception depending on how you want to handle errors
            return null;
        }
    }


    public function closeCycle($investmentId)
    {
        $investment = Investment::findOrFail($investmentId);

        // Update the investment status to 'closed' or similar
        $investment->status="closed";

        $investment->save();
        // Find the latest cycle associated with the investment
        $latestCycle = $investment->cycles()->latest()->first();

        // Update the end date of the latest cycle to the current date
        if ($latestCycle) {

            $latestCycle->cycle_end_date=now();
        }
        $latestCycle->save();
        return response()->json(['message' => 'Investment and associated cycle closed successfully'], 200);
    }
//Change investment cycle
public function ChangeInvestmentCycle($investmentId,Request $request){
    $validatedData = $request->validate([

        'cycle_start_date' => 'sometimes|date',
        'cycle_end_date' => 'sometimes|date',


    ]);

    $investment = Investment::findOrFail($investmentId);
    $latestCycle = $investment->cycles()->latest()->first();
 
    $latestCycle->update($validatedData);
    return response()->json(['message' => 'Investment cycle changed successfully','latestCycle'=>$latestCycle], 200);

}


}
