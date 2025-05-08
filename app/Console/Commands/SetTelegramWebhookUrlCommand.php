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
        $url = config('telegram.bots.mybot.webhook_url');

        $this->info('Setting webhook URL to: ' . $url);

        $response = Telegram::setWebhook(['url' => config('telegram.bots.mybot.webhook_url')]);

        if ($response) {
            $this->info('Webhook URL set successfully.');
        } else {
            $this->error('Failed to set webhook URL.');
        }
    }
}
