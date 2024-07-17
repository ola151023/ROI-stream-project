<?php

namespace App\Services;

use App\Models\Investment;
use Illuminate\Support\Facades\Log;

class InvestmentGroupingService
{
    public function groupClosedInvestments($closedInvestments)
    {
        // Calculate total profits for each user
        $userProfits = [];

        foreach ($closedInvestments as $investment) {
            $userId = $investment->user_id;

            if (!isset($userProfits[$userId])) {
                $userProfits[$userId] = 0;
            }

            // Add profit from the investment to the user's total profits
            $userProfits[$userId] += $investment->calculateProfitPerInvestment();
            Log::info("Added profit to user profile: {$userProfits[$userId]}, user id: {$userId}");
        }

        // Group investments into cycles by user ID
        $cycles = [];

        foreach ($closedInvestments as $investment) {
            $userId = $investment->user_id;

            if (!isset($cycles[$userId])) {
                $cycles[$userId] = [];
            }

            $cycles[$userId][] = $investment;
        }

        // Return the grouped data as an array, not as a JSON response
        return [
            'user_profits' => $userProfits,
            'virtual_cycles' => $cycles,
        ];
    }
}
