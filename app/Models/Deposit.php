<?php

namespace App\Models;

use App\Observers\ActivityLogObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ActivityLogObserver::class])]
class Deposit extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = ['investment_id', 'amount', 'date'];


    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];






    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    // In Deposit Model




    // In Deposit Model
    // Inside the Deposit model

    public static function calculateAccumulatedProfitPerDeposit()
    {
        // Retrieve all deposits
        $deposits = self::all();

        // Initialize an array to store accumulated profit per deposit
        $accumulatedProfits = [];

        // Loop through each deposit
        foreach ($deposits as $deposit) {
            // Retrieve the associated investment for the deposit
            $investment = $deposit->investment;

            // If the associated investment exists, calculate its profit
            if ($investment) {
                // Calculate profit for the deposit based on profit percentage and amount
                $profit = ($deposit->amount * $investment->profit_percentage) / 100;

                // Add the profit to the accumulated profits array, using the deposit ID as the key
                $accumulatedProfits[$deposit->id] = $profit;
            }
        }

        // Return the accumulated profits array
        return $accumulatedProfits;
    }

}
