<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{

    protected $fillable = [
        'type',
        'user_id',
        'model_id',
        'amount',
        'status',
        'roi_amount',

    ];
    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Investment model
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
