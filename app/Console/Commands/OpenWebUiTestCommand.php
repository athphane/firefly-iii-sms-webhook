<?php

namespace App\Console\Commands;

use App\Support\FireflyIII\Entities\ParsedTransactionMessage;
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
        $parsed_transaction = $this->callAI();

        $thing = $parsed_transaction->createTransactionOnFirefly();

        dd($thing);
    }

    /**
     * @throws ConnectionException
     */
    public function callAI(): ParsedTransactionMessage
    {
        $response = Http::baseUrl(config('openwebui.base_url'))
            ->acceptJson()
            ->withToken(config('openwebui.api_key'))
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
            ])
            ->json('choices.0.message.content');

        // dd($response->json());

        $data = json_decode($response, true);

        return ParsedTransactionMessage::make($this->getSampleTransaction(), $data);
    }

    public function getSystemMessage(): string
    {
        return <<<EOD
You are a companion piece of a larger system that helps me to categorize my day to day transactions.
I will give you a sample set of Transaction Alert Messages that I receive from my bank.
Each of the transaction messages will contain what card the transaction was on,
the date and time of the transaction, the currency and amount of the transaction,
where the transaction was taken place, and other information such as approval codes and reference number.
Your task is it to extract out the important details of each transaction.
You should output each transaction as a json object. Give the json object as as string. The json object you return MUST have the following keys: card,date,time,currency,amount,location,approval_code,reference_no.
If you cannot find any of the above keys, please return null.
The system that uses you will parse it into json and go on from there. Please do not do any markdown formatting.
EOD;
    }

    public function getSampleTransaction(): string
    {
        return "Transaction from 9516 on 01/01/25 at 13:32:59 for USD10.00 at LINODE . AKAMAI           was processed. Reference No:500108578364, Approval Code:578364.";
    }
}
