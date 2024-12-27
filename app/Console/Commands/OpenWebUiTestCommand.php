<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenWebUiTestCommand extends Command
{
    protected $signature = 'test:transaction';

    protected $description = 'Command description';

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $response = Http::baseUrl(config('openai.openwebui.base_url'))
            ->acceptJson()
            ->withToken(config('openai.openwebui.api_key'))
            ->post('chat/completions', [
                'stream'   => false,
                'model'    => 'google_genai.gemini-2.0-flash-exp',
                'messages' => [
                    [
                        'role'    => 'user',
                        'content' => $this->getSampleTransaction(),
                    ],
                    [
                        'role'    => 'system',
                        'content' => $this->getSystemMessage()
                    ],
                ],
            ]);

        dd($response->json());
    }

    public function getSystemMessage(): string
    {
        return "You are a companion piece of a larger system that helps me to categorize my day to day transactions. I will give you a sample set of Transaction Alert Messages that I receive from my bank. Each of the transaction messages will contain what card the transaction was on, the date and time of the transaction, the currency and amount of the transaction, where the transaction was taken place, and other information such as approval codes and reference number. Your task is it to extract out the important details of each transaction. You should output each transaction as a json object. Please do not do any markdown formatting. Give the json object as as string. The system that uses you will parse it into json and go on from there.";
    }

    public function getSampleTransaction(): string
    {
        return "Transaction from 9516 on 01/08/23 at 18:07:29 for MVR69.50 at FRESH SEASON was processed. Reference No:080149405072, Approval Code:405072.";
    }
}
