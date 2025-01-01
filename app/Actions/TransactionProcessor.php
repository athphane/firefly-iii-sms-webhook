<?php

namespace App\Actions;

use App\Models\Transaction;
use App\Models\Vendor;
use App\Notifications\SendTransactionCreatedNotification;
use App\Support\FireflyIII\Entities\ParsedTransactionMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class TransactionProcessor
{
    /**
     * @throws ConnectionException
     */
    public function getParsedTransactionMessage(string $raw_transaction_message): ParsedTransactionMessage
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
                        'content' => $raw_transaction_message,
                    ],
                    [
                        'role'    => 'system',
                        'content' => $this->getSystemMessage()
                    ],
                ],
            ])
            ->json('choices.0.message.content');

        $data = json_decode($response, true);

        return ParsedTransactionMessage::make($raw_transaction_message, $data);
    }

    /**
     * @throws ConnectionException
     */
    public function handle(Transaction $transaction): void
    {
        $parsed_transaction = $this->getParsedTransactionMessage($transaction->message);

        $transaction->card = $parsed_transaction->card;
        $transaction->transaction_at = $parsed_transaction->getDate();
        $transaction->currency = $parsed_transaction->getCurrency()->value;
        $transaction->amount = $parsed_transaction->amount;
        $transaction->location = $parsed_transaction->location;
        $transaction->approval_code = $parsed_transaction->approval_code;
        $transaction->reference_no = $parsed_transaction->reference_no;

        $related_vendor = Vendor::where('firefly_account_id', $parsed_transaction->getFirstSimilarAccountId())->first();
        if ($related_vendor) {
            $transaction->vendor()->associate($related_vendor);
        }

        $transaction->save();

        $firefly_transaction = $parsed_transaction->createTransactionOnFirefly();
        $firefly_transaction_id = data_get($firefly_transaction, 'data.id');

        $transaction->firefly_transaction_id = $firefly_transaction_id;
        $transaction->save();

        Notification::route('telegram', env('TELEGRAM_NOTIF_USER_ID'))
            ->notifyNow(new SendTransactionCreatedNotification($transaction));

    }

    private function getSystemMessage(): string
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
}
