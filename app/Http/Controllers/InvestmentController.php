<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvestmentResource;
use App\Models\Investment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Carbon;
class InvestmentController extends Controller
{

    public function index()
    {
        $investments = Investment::all();
        return InvestmentResource::collection($investments);
    }

    public function allUserInvestments($id)
    {
        $user=User::findOrFail($id);
        $investments =$user->investments()->get();

        return  response()->json([ 'user'=>$user,'investments' => $investments]);
    
    }

    public function store(Request $request)
    {


        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'profit_percentage' => 'required|numeric',
            'cycle_days' => 'required|numeric',
            'user_id'=>'nullable'

        ]);

   
        if($request->has('user_id')){
            $user_id=$request->user_id;

        }
       
       else
        {
            $user_id = Auth::id();
            $validatedData['user_id'] = $user_id;
        }
       
       
        $validatedData['deposit_date'] = Carbon:: now();
        $validatedData['status'] = "open";
        $validatedData['contract_duration'] = 30;

        // Create the investment
        $investment = Investment::create($validatedData);
        $investment->deposit_date= $validatedData['deposit_date'];
       
        $investment->save();
      
        // Handle deposit transaction
        $depositController = new DepositController();
        $depositController->handleDepositTransaction($investment, 
            [
               'amount' => $validatedData['amount'],
                'date' => Carbon::now(),
        
            ],$validatedData["user_id"]);

        // Create investment cycle
        $investmentCycleController = new InvestmentCycleController();
        $investmentCycleController->creatingInvestmentCycle($investment->id, $validatedData['cycle_days']);

        return new InvestmentResource($investment);
    }

    public function show($id)
    {
        try {
            $investment = Investment::findOrFail($id);

            return new InvestmentResource($investment);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['error' => 'Investment not found'], Response::HTTP_NOT_FOUND);
        }
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'amount' => 'sometimes|numeric',
            'deposit_date' => 'sometimes|date',
            'profit_percentage' => 'sometimes|numeric',
            'cycle_days' => 'sometimes|integer',
            'profit_withdrawal_limit_date' => 'sometimes|date',
        ]);

       
        $user_id = Auth::id();
        $validatedData['user_id'] = $user_id;
        $validatedData['deposit_date']=Carbon::parse($validatedData['deposit_date']);
    
        try {
            $investment = Investment::findOrFail($id);
            if($request->has("profit_withdrawal_limit_date")){
                $investment->profit_withdrawal_limit_date = $validatedData['profit_withdrawal_limit_date'];

            }
         
            // Update the investment details
            $investment->update($validatedData);
            $investment->save();
           
            return response()->json(['investment' => $investment]);
        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Update failed'], Response::HTTP_NOT_FOUND);
        }
    }
    public function destroy(Investment $investment)
    {
        $investment->delete();
        return response()->json(["message" => "investment deleted"], 204);


    }

    public function requestRenewal($id)
    {
        $investment = Investment::findOrFail($id);
        $investment->renewal_requested_at = now(); // Set the renewal_requested_at attribute directly
        $investment->renewal_status = 'requested'; // Set the renewal_status attribute
        $investment->save(); // Save the changes to the database
        return response()->json(['message' => 'Renewal requested successfully']);
    }

    public function approveRenewal($id)
    {
        $investment = Investment::findOrFail($id);
        $investment->renewal_approved_at = now(); // Set the renewal_approved_at attribute directly
        $investment->renewal_status = 'approved'; // Set the renewal_status attribute
        $investment->status = 'open';
        $investment->save(); // Save the changes to the database

        $investmentCycleController = new InvestmentCycleController();
        $investmentCycleController->creatingInvestmentCycle($investment->id, $investment->cycle_days);


        return response()->json(['message' => 'Renewal approved successfully', 'investment' => $investment]);
    }


    public function cancelRenewal($id)
    {
        $investment = Investment::findOrFail($id);
        $investment->renewal_status = 'cancelled'; // Set the renewal_status attribute
        $investment->save(); // Save the changes to the database
        return response()->json(['message' => 'Renewal cancelled successfully']);
    }

    public function requestClosing($id)
    {
        $investment = Investment::findOrFail($id);

        // Check if the investment is eligible for closing (e.g., status is active)
        if ($investment->status !== 'open') {
            return response()->json(['message' => 'Investment cannot be closed at the moment'], 400);
        }


        // Update the investment status to 'pending_closure'

        $investment->status = 'pending_closure';

        return response()->json(['message' => 'Request for closing investment submitted successfully'], 200);
    }

    public function changeProfitPercentage(Request $request, $id)
    {

        $validatedData = $request->validate([

            'profit_percentage' => 'required|numeric',

        ]);
        $investment = Investment::findOrFail($id);
        $investment->profit_percentage = $validatedData['profit_percentage'];
        $investment->save();
        return response()->json(['message' => 'Profit percentage changed successfully', 'investment-profit-percentage' => $investment->profit_percentage]);
    }


    public function setContractDuration(Request $request, $investmentId)
    {
        // Validate the request
        $request->validate([
            'contract_duration' => 'required|in:90,180,360', // Validate that the duration is one of 90, 180, or 360 days
        ]);


        $investment = Investment::findOrFail($investmentId);

        // Calculate the new profit withdrawal limit date based on the updated contract duration
        $newWithdrawalLimitDate = $investment->profit_withdrawal_limit_date->addDays($request->contract_duration - $investment->contract_duration);
        // Update the investment record with the provided contract duration
        $investment->contract_duration = $request->contract_duration;
        $investment->profit_withdrawal_limit_date = $newWithdrawalLimitDate;
        $investment->maturity_date=$investment->calculateMaturityDate();
        $investment->save();

        // Return a success response
        return response()->json(['message' => 'Contract duration set successfully']);
    }






}
