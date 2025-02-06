<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetTelegramWebhookUrlCommand extends Command
{
    protected $signature = 'telegram:set-webhook';

    protected $description = 'Set the telegram bot\'s webhook URL.';

    public function handle(): void
    {
        $response = Telegram::setWebhook([
            'url' => route('telegram.webhook'),
        ]);
    }
}
