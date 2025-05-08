<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetTelegramWebhookUrlCommand extends Command
{
    protected $signature = 'telegram:set-webhook-url';

    protected $description = 'Command description';

    /**
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $response = Telegram::setWebhook(['url' => config('telegram.bots.mybot.webhook_url')]);

        $this->line($response);
    }
}
