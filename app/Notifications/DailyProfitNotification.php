<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DailyProfitNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $dailyProfit;

    /**
     * Create a new notification instance.
     *
     * @param $dailyProfit
     */
    public function __construct($dailyProfit)
    {
        $this->dailyProfit = $dailyProfit;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast'];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'daily_profit' => $this->dailyProfit,
            // Add any other data you want to send to the frontend
        ]);
    }
}
