<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Investment;
use App\Models\DailyProfit;
use App\Notifications\DailyProfitNotification;
use Illuminate\Support\Facades\Notification;

class CalculateDailyProfits extends Command
{
    protected $signature = 'calculate:daily-profits';

    protected $description = 'Calculate daily profits for investments and trigger notifications';

    public function handle()
    {
        // Get all active investments
        $investments = Investment::where('status', 'open')->get();

        foreach ($investments as $investment) {
            // Calculate daily profit for each investment
            $dailyProfit = $this->calculateDailyProfit($investment);

            // Save the daily profit to the database
            DailyProfit::create([
                'investment_id' => $investment->id,
                'amount' => $dailyProfit,
                'date' => now()->toDateString(), // Use the current date
            ]);



             // Trigger notification to the investor
            Notification::send($investment->user, new DailyProfitNotification($dailyProfit));

        }

        $this->info('Daily profits calculated and notifications triggered successfully.');
    }
    public function calculateDailyProfit(Investment  $investment )
    {
        // Assuming daily profit is calculated based on a fixed percentage
        $dailyPercentage = $investment->profit_percentage / 31; // Divide by 31 for daily profit

        // Calculate daily profit based on the total investment amount
        $dailyProfit = ($investment->deposits()->sum('amount') * $dailyPercentage) / 100;

        return $dailyProfit;
    }
}
