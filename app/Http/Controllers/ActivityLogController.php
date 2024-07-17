<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{

    public function viewActivities(Request $request)
    {

        // Validate the request data
        $request->validate([
            'type' => 'sometimes|string|in:Investment,User,Withdrawal,Deposit',
            'min_amount' => 'sometimes|numeric',
            'max_amount' => 'sometimes|numeric',
            'min_roi_amount' => 'sometimes|numeric',
            'max_roi_amount' => 'sometimes|numeric',
            'status' => 'sometimes|string',

        ]);


        // Apply filters based on request parameters
        if ($request->has('type')) {
            Log::info($request->has('type'));
            $records = $this->getting_records($request->type);
            return response()->json(['requiredRecords' => $records]);
        }


        if ($request->has('min_amount') || $request->has('max_amount')) {
            Log::info('Getting record according to minimum and maximum amount'."  ". $request->get('min_amount') ."   ". $request->get('max_amount'));
            $records = $this->getting_amount($request->min_amount, $request->max_amount);
            return response()->json(['requiredRecords' => $records]);
        }


        if ($request->has('min_roi_amount') || $request->has('max_roi_amount')) {

            $records = $this->getting_roi_amount($request->min_roi_amount, $request->max_roi_amount);
            return response()->json(['requiredRecords' => $records]);
        }
        if ($request->has('status')) {
            $records = $this->getting_status($request->status);
            return response()->json(['requiredRecords' => $records]);
        }


    }

    public function getting_records($type)
    {
        $requiredRecords = ActivityLog::where('type', $type)
            ->get();
        return $requiredRecords;
    }

    public function getting_status($status)
    {

        $requiredRecords = [];

        // Query the ActivityLog table for investments
        $logs = ActivityLog::where('type', 'Investment')->get();
        $counter=0;
        // Loop through each activity log
        foreach ($logs as $log) {

            $investments= Investment::where('id', $log->model_id)->
            where('status', $status)->get();
            // Append the investments from this iteration to $requiredRecords
            $requiredRecords = array_merge($requiredRecords, $investments->toArray());
        }

        return $requiredRecords;
    }






    public function getting_roi_amount($min_roi_amount, $max_roi_amount)
    {


        $filteredLogs = ActivityLog::where('type', 'Investment')
            ->get();


        $filteredLogs = $filteredLogs->filter(function ($log) use ($min_roi_amount, $max_roi_amount) {
            // Retrieve investment record using relationship
            $investment = Investment::find($log->model_id);

            // Calculate ROI amount for the investment
            $calculatedROI = $investment->calculateROI();

            // Check if ROI amount falls within the provided range
            if ($min_roi_amount !== null && $max_roi_amount !== null) {
                return $calculatedROI >= $min_roi_amount && $calculatedROI <= $max_roi_amount;
            } elseif ($min_roi_amount !== null) {
                return $calculatedROI >= $min_roi_amount;
            } elseif ($max_roi_amount !== null) {
                return $calculatedROI <= $max_roi_amount;
            }

            // If both $min_roi_amount and $max_roi_amount are null, return true for all logs
            return true;
        });

        return $filteredLogs;
    }

    public function getting_amount($min_amount, $max_amount)
    {
        $filteredLogs = ActivityLog::where('type', 'Investment')
        ->get();

        $filteredLogs = $filteredLogs->filter(function ($log) use ($min_amount, $max_amount) {
            // Retrieve investment record using relationship
            $investment = Investment::find($log->model_id);

            // Calculate ROI amount for the investment
            $amount = $investment->amount;

            // Check if ROI amount falls within the provided range
            if ($min_amount !== null && $max_amount !== null) {
                return $amount >= $min_amount && $amount <= $max_amount;
            } elseif ($min_amount !== null) {
                return $amount >= $min_amount;
            } elseif ($max_amount !== null) {
                return $amount <= $max_amount;
            }

            // If both $min_roi_amount and $max_roi_amount are null, return true for all logs
            return true;
        });

        return $filteredLogs;

    }




}
