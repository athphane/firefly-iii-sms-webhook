<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TestTransactionCommand extends Command
{
    protected $signature = 'transaction:test';

    protected $description = 'Command description';

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $response = Http::withToken(env('SELF_API_KEY'))
            ->acceptJson()
            ->post('http://firefly-sms-webhook.test/api/transactions/webhook', [
                'message' => 'Transaction from 9516 on 06/05/24 at 05:45:18 for MVR434.67 at STELCO                    was processed. Reference No:412700179797, Approval Code:179797.'
            ]);


        $this->info('Transaction webhook sent.');

        $this->info($response->body());

    }
}
