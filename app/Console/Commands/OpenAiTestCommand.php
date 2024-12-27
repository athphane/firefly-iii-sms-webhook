<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI\Client;
use OpenAI\Factory as OpenAiFactory;

class OpenAiTestCommand extends Command
{
    protected $signature = 'test:openai';

    protected $description = 'Command description';

    public function getClient(): Client
    {
        /** @var Client $client */
        return (new OpenAiFactory)
            ->withBaseUri(config('openai.openwebui.base_url'))
            ->withApiKey(config('openai.openwebui.api_key'))
            ->make();
    }

    public function handle(): void
    {
        $client = $this->getClient();

        $result = $client->chat()
            ->create([
                'stream' => false,
                'model'    => 'google_genai.gemini-2.0-flash-exp',
                'messages' => [
                    [
                        'role'    => 'user',
                        'content' => $this->getSampleTransaction(),
                    ],
                    [
                        'role'    => 'system',
                        'content' => 'You are a companion piece of a larger system that helps me to categorize my day to day transactions. I will give you a sample set of Transaction Alert Messages that I receive from my bank. Each of the transaction messages will contain what card the transaction was on, the date and time of the transaction, the currency and amount of the transaction, where the transaction was taken place, and other information such as approval codes and reference number. Your task is it to extract out the important details of each transaction. You should output each transaction as a json object.'
                    ],
                ],
            ]);

        dd($result);
    }

    public function getSampleTransaction(): string
    {
        return "Transaction from 9516 on 01/08/23 at 18:07:29 for MVR69.50 at FRESH SEASON was processed. Reference No:080149405072, Approval Code:405072.";
    }
}
