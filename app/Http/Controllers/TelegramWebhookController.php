<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    public function handle($telegram_token, Request $request)
    {
        if ($telegram_token === config('telegram.bots.mybot.token')) {
            $update = Telegram::getWebhookUpdate();

            Log::info($update->keys());
        }

        return 'ok';
    }
}
