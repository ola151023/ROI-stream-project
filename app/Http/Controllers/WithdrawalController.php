<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Investment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WeeklyRoi;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class WithdrawalController extends Controller
{
    public function withdrawTransction(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'amount' => 'required|numeric|min:0',

        ]);


        $user=User::findOrFail(auth()->id());
        $wallet=$user->wallet;
        // Check if the withdrawal amount exceeds the available balance
        $availableBalance =$wallet->balance;
        if ($validatedData['amount'] > $availableBalance) {
            return response()->json(['message' => 'Insufficient funds'], 400);
        }

        // Deduct the withdrawal amount from the wallet balance
       $wallet->balance -= $validatedData['amount'];
       $wallet->save();

        // Create a withdrawal record
        Withdrawal::create([
            'investment_id' => $validatedData['investment_id'],
            'amount' => $validatedData['amount'],
            'withdrawal_date' => now(),
            'status' => 'confirmed',
        ]);



        return response()->json(['message' => 'Withdrawal successful'], 200);
    }




    private function calculateWeeklyProfit(Investment $investment)
    {
        // Assuming the investment has a profit percentage and amount
        $profitPercentage = $investment->profit_percentage;
        $investmentAmount = $investment->amount;
    
        // Calculate weekly profit based on the profit percentage and investment amount
        $weeklyProfit = ($investmentAmount * ($profitPercentage / 100)) / 52; // 52 weeks in a year

        return $weeklyProfit;
    }
    
 
    public function approveWithdrawal(Request $request, $withdrawalId)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'status' => 'required|in:confirmed,rejected',
        ]);

        // Find the withdrawal request
        $withdrawal = Withdrawal::findOrFail($withdrawalId);

        // Check if the withdrawal request is already processed
        if ($withdrawal->status !== 'pending') {
            return response()->json(['message' => 'This withdrawal request has already been processed'], 400);
        }

        // Update the withdrawal request status
        $withdrawal->status = $validatedData['status'];
        $withdrawal->save();

        return response()->json(['message' => 'Withdrawal request processed successfully'], 200);
    }


    public function isEligibleForWithdrawal($investment)
    {
        $dueDate = $investment->calculateMaturityDate($investment);
      
        $currentDate = Carbon::now();
     
        return $currentDate->greaterThanOrEqualTo($dueDate);
    }

    public function getWeeklyProfits($investment)
    {
        $currentDate = now();

        $startDate = $investment->getDepositDate();
        $weeksPassed = abs($currentDate->diffInWeeks($startDate));



        $profits = [];
        for ($week = 1; $week <= $weeksPassed; $week++) {
      
            // Check if profit for the week exists in the database
            $profit = WeeklyRoi::where('investment_id', $investment->id)
                ->where('week_number', $week)
                ->first();
            
            if (!$profit) {
               
                // Calculate profit if not found
                $profitAmount = $this->calculateWeeklyProfit($investment);

                $profit = new WeeklyRoi([
                    'investment_id' => $investment->id,
                    'week_number' => $week,
                    'roi_amount' => $profitAmount,
                ]);
           
                $profit->save();
            }

            $profits[] = $profit;
         
        }
   
        return $profits;
    }

    public function requestWithdrawalProfits($investment_id)
    {
     

        DB::beginTransaction();
        try {
            // Find the investment
            $investment = Investment::find($investment_id);

            // Check if eligible for withdrawal
            if (!$this->isEligibleForWithdrawal($investment)) {
                return response()->json(['message' => 'Investor is not eligible to withdraw weekly profit yet'], 403);
            }

            // Get weekly profits
            $weeklyProfits = $this->getWeeklyProfits($investment);
   
            $totalAmount = 0;
            foreach ($weeklyProfits as $profit) {
                $totalAmount += $profit->roi_amount;
            }
            
     
            // Create withdrawal request
            $withdrawal = Withdrawal::create([
                'user_id' => auth()->user()->id,
                'investment_id' => $investment->id,
                'amount' => $totalAmount,
                'withdrawal_date' => now(),
                'status' => 'confirmed',//we will make it pending  in the second level
            ]);

            DB::commit();



            return response()->json(['message' => 'Withdrawal request submitted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error requesting profit withdrawal: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing the request'], 500);
        }
    }
}




