<?php

namespace App\Support\FireflyIII;

use App\Support\FireflyIII\Facades\FireflyIII;
use Carbon\CarbonImmutable;

class ParsedTransactionMessage
{
    public string $raw_transaction_message;
    public string $card;
    public string $date;
    public string $time;
    public string $currency;
    public float $amount;
    public string $location;
    public string $approval_code;
    public string $reference_no;

    public static function make(string $raw_transaction_message, array $data): self
    {
        return new self(
            $raw_transaction_message,
            $data['card'],
            $data['date'],
            $data['time'],
            $data['currency'],
            $data['amount'],
            $data['location'],
            $data['approval_code'],
            $data['reference_no']
        );
    }

    public function __construct(
        string $raw_transaction_message,
        string $card,
        string $date,
        string $time,
        string $currency,
        float  $amount,
        string $location,
        string $approval_code,
        string $reference_no
    )
    {
        $this->raw_transaction_message = $raw_transaction_message;
        $this->card = $card;
        $this->date = $date;
        $this->time = $time;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->location = $location;
        $this->approval_code = $approval_code;
        $this->reference_no = $reference_no;
    }

    public function getDate(): CarbonImmutable
    {
        return CarbonImmutable::parse("$this->date $this->time");
    }

    public function getSimilarAccounts(): array
    {
        $raw_accounts = FireflyIII::getAccountsAutocomplete($this->location);

        $accounts = [];
        foreach ($raw_accounts as $raw_account) {
            $accounts[] = [
                'name' => $raw_account['name'],
                'id'   => $raw_account['id'],
            ];
        }

        return $accounts;
    }

    public function getFirstSimilarAccountId(): string
    {
        return $this->getSimilarAccounts()[0]['id'];
    }

    public function getFirstSimilarAccountName(): string
    {
        $similar_accounts = $this->getSimilarAccounts();

        if (count($similar_accounts) === 0) {
            return str($this->location)->lower()->toString();
        }

        return $similar_accounts[0]['name'];
    }

    public function getSimilarTransactionDescriptions(): array
    {
        $raw_transactions = FireflyIII::getTransactionsFromAccount($this->getFirstSimilarAccountId());

        $transaction_descriptions = [];

        foreach ($raw_transactions['data'] as $raw_transaction) {
            $inner_transactions = $raw_transaction['attributes']['transactions'];

            foreach ($inner_transactions as $inner_transaction) {
                $transaction_descriptions[] = $inner_transaction['description'];
            }
        }

        return $transaction_descriptions;
    }

    public function getPossibleTransactionDescription(): string
    {
        return collect($this->getSimilarTransactionDescriptions())
            ->unique()
            ->first();
    }

    public function getPossibleCategories(): array
    {
        $raw_transactions = FireflyIII::getTransactionsFromAccount($this->getFirstSimilarAccountId());

        $transaction_categories = [];

        foreach ($raw_transactions['data'] as $raw_transaction) {
            $inner_transactions = $raw_transaction['attributes']['transactions'];

            foreach ($inner_transactions as $inner_transaction) {
                $transaction_categories[] = $inner_transaction['category_id'];
            }
        }

        return $transaction_categories;
    }

    public function getFirstPossibleCategoryId(): string
    {
        return collect($this->getPossibleCategories())
            ->unique()
            ->filter(fn($category) => $category !== null)
            ->first();
    }

    public function createTransactionOnFirefly(): array
    {
        return FireflyIII::createTransaction($this);
    }
}
