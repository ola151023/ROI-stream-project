<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProfit extends Model
{
    use HasFactory;
    protected $fillable=[
        'investment_id',
        'amount' ,
        'date'];
    public function investment(){
        return $this->belongsTo(Investment::class);
    }
}
