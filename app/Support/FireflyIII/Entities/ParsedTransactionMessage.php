<?php

namespace App\Support\FireflyIII\Entities;

use App\Models\Vendor;
use App\Support\FireflyIII\Enums\Currencies;
use App\Support\FireflyIII\Facades\FireflyIII;
use Carbon\CarbonImmutable;

class ParsedTransactionMessage
{
    public ?string $raw_transaction_message = null;
    public ?string $card;
    public ?string $date;
    public ?string $time;
    public ?string $currency;
    public ?float $amount;
    public ?string $location;
    public ?string $approval_code;
    public ?string $reference_no;
    public bool $is_receipt = false;

    public static function make(array $data): self
    {
        return new self(
            data_get($data, 'card'),
            data_get($data, 'date'),
            data_get($data, 'time'),
            data_get($data, 'currency'),
            data_get($data, 'amount'),
            data_get($data, 'location'),
            data_get($data, 'approval_code'),
            data_get($data, 'reference_no'),
        );
    }

    public function __construct(
        ?string $card,
        ?string $date,
        ?string $time,
        ?string $currency,
        ?float  $amount,
        ?string $location,
        ?string $approval_code,
        ?string $reference_no
    )
    {
        $this->card = $card;
        $this->date = $date;
        $this->time = $time;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->location = $location;
        $this->approval_code = $approval_code;
        $this->reference_no = $reference_no;
    }

    public function getCurrency(): Currencies
    {
        return Currencies::from($this->currency);
    }

    public function isForeignTransaction(): bool
    {
        return $this->getCurrency() !== Currencies::MVR;
    }

    public function localAmount(): float
    {
        return round($this->amount * $this->getCurrency()->exchangeRate(), 2);
    }

    public function getDate(bool $is_receipt = false): CarbonImmutable
    {
        if ($is_receipt) {
            $this->is_receipt = true;
        }

        $format = $this->is_receipt ? 'd/m/Y H:i' : 'd/m/y H:i:s';

        return CarbonImmutable::createFromFormat($format, "$this->date $this->time");
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

    public function getFirstSimilarAccountId(): ?string
    {
        $similar_accounts = Vendor::withAliases($this->location)->get();

        if ($similar_accounts->count() === 0) {
            return null;
        }

        return $similar_accounts->first()->firefly_account_id;
    }

    public function getFirstSimilarAccountName(): string|int
    {
        $similar_accounts = Vendor::withAliases($this->location)->get();

        // Essentially create the account if it does not exist. This will be a bit annoying because it will
        // require some admin work.
        if ($similar_accounts->count() === 0) {
            return str($this->location)->title()->toString();
        }

        return $similar_accounts->first()->firefly_account_id;
    }

    public function getSimilarTransactionDescriptions(): array
    {
        $first_similar_account_id = $this->getFirstSimilarAccountId();

        if ($first_similar_account_id === null) {
            return [];
        }

        $raw_transactions = FireflyIII::getTransactionsFromAccount($first_similar_account_id);

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
            ->first() ?? 'ADD DESCRIPTION TO THIS TRANSACTION';
    }

    /**
     * Get all possible categories for the transaction.
     *
     * @return array
     */
    public function getPossibleCategories(): array
    {
        $transaction_categories = [];

        // Set category to null if no category was found
        if ($this->getFirstSimilarAccountId() === null) {
            return $transaction_categories;
        }

        $raw_transactions = FireflyIII::getTransactionsFromAccount($this->getFirstSimilarAccountId());

        foreach ($raw_transactions['data'] as $raw_transaction) {
            $inner_transactions = $raw_transaction['attributes']['transactions'];

            foreach ($inner_transactions as $inner_transaction) {
                $transaction_categories[] = $inner_transaction['category_id'];
            }
        }

        return $transaction_categories;
    }

    /**
     * Find out the first possible category id for the transaction.
     *
     * @return string|null
     */
    public function getFirstPossibleCategoryId(): ?string
    {
        return collect($this->getPossibleCategories())
            ->unique()
            ->filter(fn($category) => $category !== null)
            ->first() ?? null;
    }

    public function createTransactionOnFirefly(): array
    {
        return FireflyIII::createTransaction($this);
    }
}
