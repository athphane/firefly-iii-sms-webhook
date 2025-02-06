<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    public function handle($telegram_token, Request $request)
    {
        if ($telegram_token === config('telegram.bot_token')) {
            $updates = Telegram::getWebhookUpdates();

            dd($updates);
        }


        return 'ok';
    }
}
