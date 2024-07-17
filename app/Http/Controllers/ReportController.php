 <!-- <?php 

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\User;
// use Illuminate\Support\Facades\DB;
// class ReportController extends Controller
// {
//     // Function to get investors by wallet amount
//     public function InvestorsByWalletAmount()
//     {
//         // Fetching investors data sorted by wallet amount
//         $investors = User::orderBy('wallet_amount', 'desc')->get(['name', 'wallet_amount']);

//         // Prepare the response
//         $data = $investors->map(function($investor) {
//             return [
//                 'name' => $investor->name,
//                 'wallet_amount' => $investor->wallet_amount,
//             ];
//         });

//         return response()->json($data);
//     }

//     // Function to get investors by total ROI
//     public function InvestorsByProfits()
//     {
//         // Fetching investors data sorted by total ROI
//         $investors = User::orderBy('total_roi', 'desc')->get(['name', 'total_roi']);

//         // Prepare the response
//         $data = $investors->map(function($investor) {
//             return [
//                 'name' => $investor->name,
//                 'total_roi' => $investor->total_roi,
//             ];
//         });

//         return response()->json($data);
//     }

    
//     public function InvestorsByProfitsWeeklyAvg()
//     {
//         // Fetching investors data and calculating weekly average ROI
//         $investors = User::all();

//         // Prepare the response
//         $data = $investors->map(function($investor) {
//             $weekly_avg_roi = $investor->total_roi / $investor->weeks_invested; // Assuming weeks_invested is a field in your table

//             return [
//                 'name' => $investor->name,
//                 'weekly_avg_roi' => $weekly_avg_roi,
//             ];
//         })->sortByDesc('weekly_avg_roi');

//         return response()->json($data->values()->all());
//     }

//     // Function to get weekly profits (each week as a row)
//     public function InvestorsByRoi()
//     {
//         // Assuming you have a profits table with investor_id, week, and profit fields
//         $profits = DB::table('weekly_rois')
//                     ->join('users', 'weekly_rois.user_id', '=', 'users.id')
//                     ->select('users.name', 'weekly_rois.week_number', 'weekly_rois.amount')
//                     ->orderBy('weekly_rois.week_number', 'desc')
//                     ->get();

//         return response()->json($profits);
//     }
// } 
