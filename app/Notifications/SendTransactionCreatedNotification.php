<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class SendTransactionCreatedNotification extends Notification
{
    public function __construct(public Transaction $transaction)
    {
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->to($notifiable->routes['telegram'])
            ->content("Transaction created: {$this->transaction->firefly_url}");
    }
}
