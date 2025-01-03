<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TestWebhookCommand extends Command
{
    protected $signature = 'webhook:test';

    protected $description = 'Command to test the webhook';

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $response = Http::withToken(env('SELF_API_KEY'))
            ->acceptJson()
            ->post('http://firefly-sms-webhook.test/api/transactions/webhook', [
                'message' => 'Transaction from 9516 on 02/01/25 at 14:03:37 for MVR4636.32 at DHIRAAGU                  was processed. Reference No:500209244524, Approval Code:244524.'
            ]);


        $this->info('Transaction webhook sent.');

        $this->info($response->body());

    }
}
