<?php

namespace App\Models;

use App\Observers\ActivityLogObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ActivityLogObserver::class])]
class Withdrawal extends Model
{
    use HasFactory;
    protected $fillable = ['investment_id', 'amount', 'withdrawal_date','status'];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
