<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;

    protected $fillable = ['investment_id', 'amount', 'type', 'bonus_date','expire_date'];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
