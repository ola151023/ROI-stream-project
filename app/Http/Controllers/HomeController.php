<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Investment;
use App\Models\User;
use App\Models\WeeklyRoi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PHPUnit\Framework\Exception;
use illuminate\Support\Facades\Log;
class HomeController extends Controller

{
    public function getAccountNumber(): JsonResponse
    {
        try {

            $user = User::find(auth()->id());

            $accountNumber = $user->account_number;

            return response()->json(['accountNumber' => $accountNumber]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve account number'], 500);
        }
    }

    public function getBalance(Request $request)
    {
        // try {
            if($request->has('user_id')){
                $user_id=$request->user_id;
                $user=User::find($user_id);
    
            }
           
           else
            {
                $user_id = Auth::id();
                $user=User::find($user_id);
            }
           

            $balance = $user->wallet->balance;
         
            $totalProfits=0;
            $totalDeposits=0;
            // Calculate total profits
          $investments   =  $user->investments()->get();

          foreach ($investments as $investment) {
          
        
            $deposits=$investment->deposits()->get();
            // dd($deposits);
            foreach($deposits as $deposit) {
            
                $totalDeposits+= $deposit->amount;
            }
              $totalProfits+=$investment->calculateROI();
          }

            return response()->json([
                'balance' => $balance,
                'total_deposits' => $totalDeposits,
                'total_profits' => $totalProfits
            ]);

        // } catch (\Exception $e) {
         
        //     return response()->json(['error' => 'Failed to retrieve balance'], 500);
        // }
    }


    public function CalculateTotalProfits(): float
    {
        try {
            $user = auth()->user();
// Calculate total profits of all closed investments
            $totalProfits = $user->investments()->where('status', 'closed')->sum(DB::raw('(amount * profit_percentage / 100)'));
            return $totalProfits;
        } catch (\Exception $e) {
          return  $e->getMessage();
        }
    }

    public static function calculateWeeklyProfits(): float
    {
        try {
            $startDate = Carbon::now()->subDays(7);
            $endDate = now();
            $userId = auth()->id();

            // Retrieve weekly profits for the specified user and date range
            $weeklyProfits = Investment::where('user_id', $userId)
                ->whereBetween('deposit_date', [$startDate, $endDate])
                ->sum(DB::raw('(amount * profit_percentage / 100)'));

       
            // If no investments are found within the specified date range, return 0
            if ($weeklyProfits === null) {
                return 0;
            }

            return $weeklyProfits;
            } catch (\Exception $e) {
                return
                    $e->getMessage();
            }
    }
    public function getROIDetails(Request $request)
    {
        

        // try {

            $validatedData = $request->validate([
                
                "start_date" => "required|date",
                "end_date" => "required|date",
            ]);

            // Retrieve authenticated user
            $user = auth()->user();

            // Calculate ROI and profit per investment for each investment
            $investments = $user->investments()->with('cycles')->get();
           
            foreach ($investments as $investment) {
                // Log::info($investments."investments");
            //        $investment->roi = $investment->calculateROI();
                $investment->profit_per_investment = $investment->calculateProfitPerInvestment();

            }
    
            // Calculate total profits of all closed investments
            $totalProfits = $this->CalculateTotalProfits();
            // Log::info($totalProfits."totalProfits");
            // Retrieve and filter weekly profits
            // $weeklyProfits = $this->calculateWeeklyProfits();

            $inv_id=$investment->id;

            $weeklyProfits=WeeklyRoi::where('investment_id', $inv_id)
                ->whereBetween('created_at', [$validatedData['start_date'], $validatedData['end_date']])
                ->get();

                Log::info($weeklyProfits."weeklyProfits");
            return response()->json([
                'investments' => $investments,
                'totalProfits' => $totalProfits,
                'weeklyProfits' => $weeklyProfits,
            ]);
        // } catch (\Exception $e) {
        //     return response()->json(['error' => $e], 500);
        // }
    }


    public function viewRoiReport($investmentId){
        $profits=WeeklyRoi::where('investment_id', $investmentId);
        $investment=Investment::findOrFail($investmentId);
        $investment->profit_per_investment =$profits;

        return response()->json(['profit_per_investment' =>  $investment->profit_per_investment], 200);
   
    }



    public function investmentsList()
    {
        // Retrieve investments for the authenticated user
        $investments = auth()->user()->investments()->get();


        $investmentDetails = [];


        foreach ($investments as $investment) {
            // Calculate ROI data (Deposit date plus 31 calendar days)

            $depositDate = Carbon::parse($investment->deposit_date);

            $roiStartDate = $depositDate->copy()->addDays(31); // Start of the ROI period
            $roiEndDate = Carbon::now(); // End of the ROI period (current date)

            // Calculate ROI for the specified period
            $roiData = $investment->calculateROIPeriod( $roiStartDate, $roiEndDate);



            $maturityDate=$investment->calculateMaturityDate();

            // Create an array with investment details
            $investmentDetails[] = [
                'deposit_date' => $investment->deposit_date,
                'amount' => $investment->amount,
                'roi_data' => $roiData,
                'maturity_date' => $maturityDate,
            ];
        }


        return response()->json([
            'investmentDetails' => $investmentDetails,
        ],200);
    }


    public static function calculateTotalROI(): JsonResponse
    {

        try {
      
            $userId=auth()->id();
         
           // Retrieve all closed deposits for the user
            $closedDeposits = Deposit::whereHas('investment', function ($query) use ($userId) {
                $query->where('status', 'closed')
                    ->where('user_id', $userId); // Filter investments by the user ID
            })
                ->get();
     


// Initialize total ROI variable
            $totalROI = 0;

// Calculate ROI for each closed deposit and sum up the total ROI
            foreach ($closedDeposits as $deposit) {
// Retrieve the associated investment for the deposit
                $investment = $deposit->investment;
// If the associated investment exists and is closed, calculate its ROI
                if ($investment) {
                    $roi = $investment->calculateROI();


                // Add the ROI to the total
                    $totalROI += $roi;
                }
            }

            return response()->json([
                'totalROI' => $totalROI,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to calculate total ROI'], 500);
        }
    }



    public function getAccumulatedProfitPerDeposit()
    {
        try {
            // Call the static method from the Deposit model
            $accumulatedProfit = Deposit::calculateAccumulatedProfitPerDeposit();

            // Return a successful response with the accumulated profit
            return response()->json(['accumulatedProfitPerDeposit' => $accumulatedProfit], 201);
        } catch (Exception $e) {
            // Handle the exception and return an error response
            return response()->json(['error' => 'An error occurred while retrieving the accumulated profit per deposit.'], 500);
        }
    }



    }
