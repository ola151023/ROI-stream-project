<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Investment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{


    public function depositTransction(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'amount' => 'required|numeric|min:0',
        ]);

        // Find the investment
        $investment = Investment::findOrFail($validatedData['investment_id']);
        // Update the wallet balance

// Assuming $userId contains the ID of the user
        $user = User::find(auth()->id());


            // Access the wallet associated with the user
            $wallet = $user->wallet;

            if ($wallet) {

                $wallet->balance += $validatedData['amount'];
                $wallet->save();
            }
            // Create a deposit record
            Deposit::create([
                'investment_id' => $investment->id,
                'amount' => $validatedData['amount'],
                'date' => now()
            ]);


        return response()->json(['message' => 'Deposit successful'], 200);
    }


    public function handleDepositTransaction(Investment $investment, $depositData,$user_id)
{
  
    try {
    

        // Retrieve the wallet associated with the user
        $wallet = User::find($user_id)->wallet;

        if ($wallet) {
            // Log wallet information
            Log::info("Wallet: " . $wallet);

            // Create deposit record
            $investment->deposits()->create([
                'amount' => $depositData['amount'],
                'date' => $depositData['date'], 
            ]);

            // Update the wallet balance
            $wallet->balance += $depositData['amount'];

            // Log updated wallet balance
            Log::info("Updated wallet balance: " . $wallet->balance);

            // Save the updated wallet
            $wallet->save();
        } else {
            // Log error if wallet is not found
            Log::error("Wallet not found for user ID: $user_id");
        }

        // Operation completed successfully
        return true;
    } catch (\Exception $e) {
        // Log the error or handle it appropriately
        Log::error("Error handling deposit transaction: " . $e->getMessage());
        return false; // Operation failed
    }
}


    // Calculate total ROI for all closed deposits associated with a specific user


//{
//    public function getAllDeposits()
//    {
//        return Deposit::all();
//    }
//
//    public function addDeposit(Request $request): JsonResponse
//    {
//
//        // Checking if the user has the permission to add the deposit:
//        // if (Auth()->user()->hasPermission('add_deposit')) {
//        //     return response()->json(['message' => 'You dont have permission to add a deposit']);
//        // }
//
//        // if ($request->user()->hasPermission('add_deposit')) {
//        //     return response()->json(['message' => 'You dont have permission to add a deposit']);
//        // }
//
//        $validatedData = $request->validate([
//            'depositor' => 'required',
//            'percentage' => 'required|numeric',
//            'due_date' => 'required|date',
//            'date_of_deposit' => 'required|date'
//
//        ]);
//
//        // Calculate maturity date (every 31 calendar days)
//        $dueDate = Carbon::parse($validatedData['due_date'])->addDays(31);
//
//        // Store deposit with user ID
//        $user = auth()->user();
//        $depositData = array_merge($validatedData, ['due_date' => $dueDate, 'user_id' => $user->id]);
//        $deposit = Deposit::create($depositData);
//
//        // Calculate wallet information
//        $depositAmount = $deposit->percentage;
//        $maturityDate = $deposit->maturity_date;
//        $accumulatedProfit = $depositAmount;
//        $accountNumber = $user->id . '.' . $deposit->id;
//
//        // Prepare wallet information
//        $wallet = Wallet::query()->create([
//            'user_id' => $user->id,
//            'deposit_id' => $deposit->id,
//            'name' => $user->name,
//            'deposit_amount' => $depositAmount,
//            'date_of_deposit' => $deposit->date_of_deposit,
//            'maturity_date' => $maturityDate,
//            'accumulated_profit' => $accumulatedProfit,
//            'account_number' => $accountNumber
//        ]);
//
//        return apiResponse(['message' => 'Deposit added successfully', 'wallet' => $wallet]);
//    }
//
//    public function getWeeklyRoi(): JsonResponse
//    {
//        $user = auth()->user();
//
//        // Calculate the sum of percentages for the user's deposits
//        $weeklyRoi = DB::table('deposits')
//            ->where('user_id', $user->id)
//            ->whereBetween('date_of_deposit', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
//            ->sum('percentage');
//
//        // Divide the weekly ROI by 7 (number of days in a week)
//        // and then further divide by 31 (number of days in a month)
//        $weeklyRoi = $weeklyRoi / 7 / 31;
//
//        return apiResponse(['weekly_ROI' => $weeklyRoi]);
//    }
//
//    // Method to calculate and return accumulated profit
//    public function getAccumulatedProfit(): JsonResponse
//    {
//        // if (!auth()->user()->hasPermission('get-accumulated-profit')) {
//        //     return response()->json(['message' => 'Permission denied'], 403);
//        // }
//
//        $user = auth()->user();
//
//        // Calculate the accumulated profit for the user's deposits
//        $accumulatedProfit = DB::table('deposits')
//            ->where('user_id', $user->id)
//            ->sum(DB::raw('(percentage / 31)'));
//
//        return apiResponse(['accumulated_profit' => $accumulatedProfit]);
//    }
//
//    public function getDepositById($id)
//    {
//        return Deposit::findOrFail($id);
//    }
//
//    public function updateDeposit(Request $request, $id): JsonResponse
//    {
//        $deposit = Deposit::findOrFail($id);
//
//        if ($deposit->cycle_closed) {
//            return apiResponse(['message' => 'Cannot update a deposit with closed cycle'], 403);
//        }
//
//        $validatedData = $request->validate([
//            'depositor' => 'sometimes',
//            'percentage' => 'sometimes|numeric',
//            'due_date' => 'sometimes|date',
//            'date_of_deposit' => 'sometimes|date',
//            'status' => 'sometimes|boolean'
//        ]);
//
//        $deposit->update($validatedData);
//        return apiResponse(['message' => 'Deposit updated successfully', 'deposit' => $deposit]);
//    }
//
//
//    public function deleteDeposit($id): JsonResponse
//    {
//        $deposit = Deposit::findOrFail($id);
//        $wallet = Wallet::query()->where('deposit_id', '=', $deposit->id);
//        $wallet->delete();
//        $deposit->delete();
//
//        return apiResponse(['message' => 'Deposit deleted successfully'], 200);
//    }
//
//    public function withdrawProfit(Request $request, $id): JsonResponse
//    {
//        // Check if the authenticated user has permission to withdraw profits
//        // if (!auth()->user()->hasPermission('withdraw_profit')) {
//        //     return response()->json(['message' => 'Permission denied'], 403);
//        // }
//
//        // Find the deposit by ID
//        $deposit = Deposit::findOrFail($id);
//
//        if ($deposit->cycle_closed) {
//            return apiResponse(['message' => 'Cannot withdraw profit from a deposit with closed cycle'], 403);
//        }
//
//        // Calculate the last withdrawal date by finding the latest withdrawal record
//        $lastWithdrawal = $deposit->withdrawals()->latest()->first();
//
//        // If there's no withdrawal yet, set the last withdrawal date to the deposit creation date
//        $lastWithdrawalDate = $lastWithdrawal ? $lastWithdrawal->created_at : $deposit->created_at;
//
//        // Calculate the difference between the last withdrawal date and the current date in days
//        $daysSinceLastWithdrawal = Carbon::now()->diffInDays($lastWithdrawalDate);
//
//        // Check if the user is trying to withdraw profit before one month
//        if ($daysSinceLastWithdrawal < 31) {
//            return apiResponse(['message' => 'You can only withdraw profit once every month'], 403);
//        }
//
//        // Calculate the amount to withdraw based on the percentage and maturity date
//        $withdrawalAmount = $deposit->percentage * 0.01;
//
//        // You may want to save the withdrawal record here
//
//        return apiResponse(['message' => 'Profit withdrawn successfully', 'amount' => $withdrawalAmount]);
//    }
//
//    public function grantWithdrawalPermission(Request $request, $userId): JsonResponse
//    {
//        // Check if the authenticated user has permission to grant withdrawal permission
//        // if (!auth()->user()->hasPermission('grant_withdrawal_permission')) {
//        //     return response()->json(['message' => 'Permission denied'], 403);
//        // }
//
//        // Find the user by ID
//        $user = User::findOrFail($userId);
//
//        // Grant withdrawal permission to the user
//        $user->givePermissionTo('withdraw_profit');
//
//        return apiResponse(['message' => 'Withdrawal permission granted successfully']);
//    }
//
//    public function closeCycle(): JsonResponse
//    {
//        // Check if the authenticated user has permission to close the cycle
//        // if (!auth()->user()->hasPermission('close_investor_cycle')) {
//        //     return response()->json(['message' => 'Permission denied'], 403);
//        // }
//
//        // Find the authenticated user
//        $user = auth()->user();
//
//        try {
//            // Find all deposits belonging to the user
//            $deposits = Deposit::where('user_id', $user->id)->get();
//
//            // Close the cycle for each deposit
//            foreach ($deposits as $deposit) {
//                $deposit->cycle_closed = true;
//                $deposit->save();
//            }
//
//            return apiResponse(['message' => 'Your cycle closed successfully']);
//        } catch (\Exception $e) {
//            // Handle any potential exceptions (e.g., database errors)
//            return apiResponse(['message' => 'An error occurred while closing the cycle'], 500);
//        }
//    }
}
