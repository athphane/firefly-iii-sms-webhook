<?php

namespace App\Support\FireflyIII;

use App\Support\FireflyIII\Entities\ParsedTransactionMessage;
use App\Support\FireflyIII\Enums\TransactionTypes;
use Illuminate\Support\Facades\Http;

class FireflyIII
{
    public string $base_url;
    public string $api_key;

    public function __construct(string $base_url, string $api_key)
    {
        $this->base_url = $base_url;
        $this->api_key = $api_key;
    }

    private function getJson(string $endpoint, string $method = 'GET', array $params = []): array
    {
        return Http::baseUrl($this->base_url)
            ->withToken($this->api_key)
            ->$method($endpoint, $params)
            ->json();
    }

    public function about(): array
    {
        return $this->getJson('/about');
    }

    public function aboutUser(): array
    {
        return $this->getJson('/about/user');
    }

    public function transactions(): array
    {
        return $this->getJson('/transactions');
    }

    public function getAccountsAutocomplete(string $query): array
    {
        return $this->getJson(endpoint: '/autocomplete/accounts',
            params: [
                'query' => $query,
                'limit' => 20,
            ]);
    }

    public function getTransactionsFromAccount(string $account_id): array
    {
        return $this->getJson(endpoint: "/accounts/$account_id/transactions");
    }

    public function createTransaction(ParsedTransactionMessage $parsed_transaction): array
    {
        return $this->getJson(
            endpoint: '/transactions',
            method: 'POST',
            params: [
                'transactions' => [
                    [
                        'type'             => TransactionTypes::WITHDRAWAL->value,
                        'date'             => $parsed_transaction->getDate()->toIso8601String(),
                        'amount'           => $parsed_transaction->amount,
                        'description'      => $parsed_transaction->getPossibleTransactionDescription(),
                        'source_id'        => config('firefly.instance.account_id'),
                        'destination_name' => $parsed_transaction->getFirstSimilarAccountName(),
                        'category_id'      => $parsed_transaction->getFirstPossibleCategoryId(),
                        'tags'             => ['powered-by-gemini'],
                        'notes'            => "Raw transaction message: $parsed_transaction->raw_transaction_message",
                    ],
                ],
            ],
        );
    }
}
