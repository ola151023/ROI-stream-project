<?php

namespace App\Observers;

use App\Http\Controllers\WithdrawalController;
use App\Models\ActivityLog;
use App\Models\Deposit;
use App\Models\Investment;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;

class ActivityLogObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the ActivityLog "created" event.
     */

        public function created($model)
    {
        try {
        // Determine activity type based on the model
        $type = $this->getActivityType($model);

        // Check if the model is a User instance and it's the first user (admin)
        if ($model instanceof User && User::count() === 1) {
            return; // Skip creating activity log for the first user
        }


        if ($type=='Investment'){

            ActivityLog::create([
                'type' => $type,
                'model_id' => $model->id,
                'status' => $model->status,
                'user_id' => auth()->id(),
                'min_amount' => $model->min_amount,
                'max_amount' => $model->max_amount,
                'min_roi_amount' => $model->min_roi_amount,
                'max_roi_amount' => $model->max_roi_amount,
            ]);
        }
        elseif($type=='User'){
            ActivityLog::create([
                'type' => 'User',
                'model_id' => $model->id,
                'user_id' => $model->id
            ]);
        }
        else{
        // Create activity record

//            dd('type' . $type,
//                'model_id' . $model->id,
//                'user_id' . $model->id);
        ActivityLog::create([
            'type' => $type,
            'model_id' => $model->id,
            'user_id' => auth()->id(),


        ]);
        }
        }
        catch (\Exception $exception){
          throw $exception;
        }

    }

        protected function getActivityType($model)
    {
        // Determine activity type based on the model type
        if ($model instanceof User) {

            return 'User';
        } elseif ($model instanceof Investment) {
            return 'Investment';
        }
        elseif ($model instanceof Deposit) {
            return 'Deposit';
        }
        elseif ($model instanceof Withdrawal) {
            return 'Withdrawal';
        }else{
            dd(get_class($model));
        }




    }


    /**
     * Handle the ActivityLog "updated" event.
     */
    public function updated($model): void
    {
        //
    }

    /**
     * Handle the ActivityLog "deleted" event.
     */
    public function deleted($model)
    {
        if ($model instanceof User) {
            // Handle User deletion
            ActivityLog::where('user_id', $model->id)->delete();
        } elseif ($model instanceof Investment) {
            // Handle Investment deletion
            ActivityLog::where('type', 'Investment')->where('model_id', $model->id)->delete();
        } elseif ($model instanceof Deposit) {
            // Handle Deposit deletion
            ActivityLog::where('type', 'Deposit')->where('model_id', $model->id)->delete();
        } elseif ($model instanceof Withdrawal) {
            // Handle Withdrawal deletion
            ActivityLog::where('type', 'Withdrawal')->where('model_id', $model->id)->delete();
        } else {
            // Handle deletion of other models
            // For example:
            // Log::info('Deleted model other than User, Investment, Deposit, or Withdrawal');
        }
    }

    /**
     * Handle the ActivityLog "restored" event.
     */
    public function restored(ActivityLog $activityLog): void
    {
        //
    }

    /**
     * Handle the ActivityLog "force deleted" event.
     */
    public function forceDeleted(ActivityLog $activityLog): void
    {
        //
    }
}
