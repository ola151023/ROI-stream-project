<?php

namespace App\Models;

use App\Models\User;
use App\Models\Deposit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

 class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'balance'];

     public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
     {
         return $this->belongsTo(User::class);
     }





//    public function deposit()
//    {
//        $this->belongsTo(Deposit::class);
//    }
}
