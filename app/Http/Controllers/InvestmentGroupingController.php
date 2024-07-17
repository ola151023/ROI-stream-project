<?php

    namespace App\Http\Controllers;
    
    use Illuminate\Http\Request;
    use App\Services\InvestmentGroupingService;
    use App\Models\Investment;
    use Illuminate\Support\Facades\Validator;
    
    class InvestmentGroupingController extends Controller
    {
        protected $investmentGroupingService;
    
        public function __construct(InvestmentGroupingService $investmentGroupingService)
        {
            $this->investmentGroupingService = $investmentGroupingService;
        }
    
        public function groupClosedInvestments(Request $request)
        {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'investment_ids' => 'required|array',
                'investment_ids.*' => 'integer|exists:investments,id',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
    
            // Get investment IDs from the request
            $investmentIds = $request->input('investment_ids');
    
            // Retrieve closed investments from the database for the given IDs
            $closedInvestments = Investment::whereIn('id', $investmentIds)
                                            ->where('status', 'closed')
                                            ->get();
          
            // Call the service method with closed investments
            $result = $this->investmentGroupingService->groupClosedInvestments($closedInvestments);
    
            return response()->json([
                'message' => 'Closed investments grouped successfully.',
                'user_profits' => $result['user_profits'],
                'virtual_cycles' => $result['virtual_cycles'],
            ]);
        }
    }
