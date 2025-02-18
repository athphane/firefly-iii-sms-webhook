<?php

namespace App\Actions;

use App\Models\Transaction;
use App\Models\Vendor;
use App\Notifications\SendTransactionCreatedNotification;
use App\Support\FireflyIII\Entities\ParsedTransactionMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Notification;
use LucianoTonet\GroqLaravel\Facades\Groq;
use LucianoTonet\GroqPHP\GroqException;

class TransactionProcessor
{
    /**
     * @throws GroqException
     */
    public function getParsedTransactionMessage(string $raw_transaction_message): ParsedTransactionMessage
    {
        $response = Groq::chat()
            ->completions()
            ->create([
                'model'    => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role'    => 'user',
                        'content' => $raw_transaction_message,
                    ],
                    [
                        'role'    => 'system',
                        'content' => $this->getSystemMessageForText()
                    ],
                ],
            ]);

        $data = $response['choices'][0]['message']['content'];

        $data = json_decode($data, true);

        return ParsedTransactionMessage::make($data);
    }

    /**
     * @throws GroqException
     */
    public function getParsedTransactionMessageViaImage(string $receipt_path): ParsedTransactionMessage
    {
        $response = Groq::vision()
            ->analyze(
                imagePathOrUrl: $receipt_path,
                prompt: $this->getSystemMessageForImage(),
                options: [
                    'model' => 'llama-3.2-11b-vision-preview'
                ]
            );

        $imageAnalysis = $response['choices'][0]['message']['content'];

        $data = json_decode($imageAnalysis, true);

        return ParsedTransactionMessage::make($data);
    }

    /**
     * @throws ConnectionException|GroqException
     */
    public function handle(Transaction $transaction): void
    {
        if ($receipt_path = $transaction->receipt_path) {
            $parsed_transaction = $this->getParsedTransactionMessageViaImage($receipt_path);
        } else {
            $parsed_transaction = $this->getParsedTransactionMessage($transaction->message);
        }

        $transaction->card = $parsed_transaction->card;
        $transaction->transaction_at = $parsed_transaction->getDate((bool)$transaction->getMedia('receipt')->first()?->exists());
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

        Notification::route('telegram', config('telegram.admin_user_id'))
            ->notifyNow(new SendTransactionCreatedNotification($transaction));

    }

    private function getSystemMessageForText(): string
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

    private function getSystemMessageForImage(): string
    {
        return <<<EOD
You are a companion piece of a larger system that helps me to categorize my day to day transactions. I will give you an image of a transaction receipt that I received from my bank. This receipt will contain a reference number of the transaction, the date and time of the transaction, the currency and amount of the transaction, and to who the transaction was sent to.
Your task is it to extract out the important details of each transaction. You should ONLY output each transaction as a json object and nothing else. Any other text that you output is not ideal at all. Make sure that string values are quoted correctly. I do not need to see your thinking process. All I need is the final JSON output of the data that I ask you to capture. If you give extra details, then that means the rest of the system will break and will not be able to move forward. The json object you return MUST have the following keys: date,time,currency,amount,location,reference_no. The location can be referred to as the "to" field in the receipt.
If you cannot find any of the above keys, please return null in the appropriate key. The system that uses you will parse it into json and go on from there. Please do not do any markdown formatting. Please do not include any other text in your response.
EOD;
    }
}
