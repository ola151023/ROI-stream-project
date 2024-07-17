<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentCycle extends Model
{
    use HasFactory;
    protected $fillable = ['investment_id', 'cycle_start_date', 'cycle_end_date'];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
