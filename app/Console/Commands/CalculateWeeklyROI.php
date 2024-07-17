<?php

namespace App\Console\Commands;

use App\Models\Investment;
use App\Models\WeeklyRoi;
use Illuminate\Console\Command;

class CalculateWeeklyROI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:weekly-roi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate weekly ROI for investments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get all investments
        $investments = Investment::all();

        foreach ($investments as $investment) {
            // Calculate weekly ROI for each investment
            $weeklyRoi = $this->calculateWeeklyROIForInvestment($investment);

            // Store the calculated weekly ROI in the database
            WeeklyRoi::create([
                'investment_id' => $investment->id,
                'week_number' => $weeklyRoi['week_number'],
                'roi_amount' => $weeklyRoi['roi_amount'],
            ]);
        }

        $this->info('Weekly ROI calculation completed successfully.');
    }

    /**
     * Calculate weekly ROI for a given investment.
     *
     * @param  \App\Models\Investment  $investment
     * @return array
     */
    protected function calculateWeeklyROIForInvestment(Investment $investment)
    {
        // Get the total investment amount
        $totalInvestmentAmount = $investment->deposits()->sum('amount');

        // Calculate the total profit based on the profit percentage
        $totalProfit = ($totalInvestmentAmount * $investment->profit_percentage) / 100;

        // Calculate weekly ROI
        $weeklyRoiAmount = $totalProfit / 31; // Divide the total profit by 31 days

        // Determine the current week number
        $weekNumber = now()->weekOfYear;


        return [
            'week_number' => $weekNumber,
            'roi_amount' => $weeklyRoiAmount,
        ];
    }
}
