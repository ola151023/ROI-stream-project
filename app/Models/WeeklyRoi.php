<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyRoi extends Model
{
    use HasFactory;

    protected $fillable = ['investment_id', 'week_number','roi_amount'];
}
