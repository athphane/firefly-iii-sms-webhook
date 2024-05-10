<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramTestCommand extends Command
{
    protected $signature = 'telegram:test';

    protected $description = 'Command description';

    public function handle(): void
    {
        $response = Telegram::getMe();
        dd($response);
    }
}
