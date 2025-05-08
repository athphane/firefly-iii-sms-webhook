<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Laravel\Facades\Telegram;
use Throwable;

class TelegramWebhookController extends Controller
{
    /**
     * @throws Throwable
     */
    public function handle($telegram_token, Request $request)
    {
        if ($telegram_token === config('telegram.bots.mybot.token')) {
            $update = Telegram::getWebhookUpdate();

            $message = $update->get('message')->get('text');

            DB::transaction(function () use ($message) {
                $transaction = new Transaction(['message' => $message]);

                if ($transaction->save()) {
                    $transaction->process();

                    $transaction = $transaction->refresh();

                    if ($transaction->firefly_url) {
                        $response = Telegram::sendMessage([
                            'chat_id' => config('telegram.admin_user_id'),
                            'text' => $transaction->firefly_url,
                        ]);
                    }
                }
            });
        }

        return 'ok';
    }
}
