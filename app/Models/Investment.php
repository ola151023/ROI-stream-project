<?php

namespace App\Models;

use App\Observers\ActivityLogObserver;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Sodium\add;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ActivityLogObserver::class])]
class Investment extends Model
{
    use HasFactory;


    protected $fillable = ['user_id', 'amount', 'deposit_date', 'profit_percentage',
        'cycle_days','maturity_date','due_profit','contract_duration',"profit_withdrawal_limit_date",
        'renewal_requested_at', 'renewal_approved_at', 'renewal_status',
    ];

  protected $casts = [
        'cycle_days' => 'integer',
    ];
    protected $dates = [
        'deposit_date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cycles()
    {
        return $this->hasMany(InvestmentCycle::class);
    }
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

public function weeklyRoi()
{
    return $this->hasMany(WeeklyRoi::class);
}
    public function getRenewalRequestedAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }


    public function getRenewalApprovedAtAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }


    public function setRenewalRequestedAtAttribute($value)
    {
        $this->attributes['renewal_requested_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    // Define mutator for renewal_approved_at
    public function setRenewalApprovedAtAttribute($value)
    {
        $this->attributes['renewal_approved_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }




    /**
     * Get the wallet associated with the investment.
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
public function bonuses()
{
    return $this->hasMany(Bonus::class);
}
public function getDepositDate()
{
    return $this->deposit_date;
}
// Investment.php

    public function calculateROI()
    {
        // Get the total deposit amount for the investment
        $totalDepositAmount = $this->deposits()->sum('amount');

        // Calculate the total profit based on the profit percentage
        $totalProfit = ($totalDepositAmount * $this->profit_percentage) / 100;

        return $totalProfit;
    }

    public function calculateROIPeriod( $startDate, $endDate )
    {
        // Get the total deposit amount for the investment
        $totalDepositAmount = $this->deposits()->
        whereBetween('date', [$startDate, $endDate])
            ->sum('amount');



        // Calculate the total profit based on the profit percentage
        $totalProfit = ($totalDepositAmount * $this->profit_percentage) / 100;

        return $totalProfit;
    }


   public  function calculateMaturityDate() {
      // Access the protected deposit_date using the getter method
      $depositDate = $this->getDepositDate();

  


        // Convert start date to DateTime object
        $startDateObj = Carbon::parse($depositDate);
       
        // Add contract duration in days to start date
       $maturityDateObj=$startDateObj->addDays($this->contract_duration);


        // Format the maturity date
        return $maturityDateObj;
    }


    public function calculateProfitPerInvestment()
    {

        // Calculate profit per investment (profit per deposit)
        $totalDeposits = $this->deposits()->sum('amount');

        $totalProfit = ($totalDeposits * $this->profit_percentage) / 100;

        return $totalProfit;
    }

    // public function calculateDueProfit(Investment $investment)
    // {
    //     // Get the current date
    //     $currentDate = now();
    //     $dueDate=$this->calculateDueDate();
    //     // Calculate the number of days until the next profit withdrawal is due
    //     $daysUntilDue = $currentDate->diffInDays($dueDate);
    //     // If the current date is past the due date, the due profit is accumulated
    //     // Otherwise, it is zero until the due date is reached
    //     ////////
    //     $dueProfit = $daysUntilDue >= 0 ? $investment->profit_percentage : 0;
    //     $investment->due_profit=$dueProfit;
    //     return $investment->due_profit;
    // }
    public function getDepositeDate(){
        return $this->dates->deposit_date;
    }
public function calculateDueDate(){

  
    $date = Carbon::parse($this->getDepositeDate());
    dd($date);

    // Calculate the due date for the next profit withdrawal
    $dueDate = $date->addDays($this->cycle_days)->toDateString();


    return $dueDate;

}


}
