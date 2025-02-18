<?php

namespace App\Support\FireflyIII;

use App\Support\FireflyIII\Entities\ParsedTransactionMessage;
use App\Support\FireflyIII\Enums\AccountTypes;
use App\Support\FireflyIII\Enums\TransactionTypes;
use Illuminate\Support\Facades\Http;

class FireflyIII
{
    public string $base_url;
    public string $api_url;
    public string $api_key;

    public function __construct(string $base_url, string $api_key)
    {
        $this->base_url = $base_url;
        $this->api_url = $this->base_url . '/api/v1';
        $this->api_key = $api_key;
    }

    private function getJson(string $endpoint, string $method = 'GET', array $params = []): array
    {
        return Http::baseUrl($this->api_url)
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
        $destination_account = $parsed_transaction->getFirstSimilarAccountName();

        // Base transaction data
        $transaction_data = [
            'type'             => TransactionTypes::WITHDRAWAL->value,
            'date'             => $parsed_transaction->getDate()->toIso8601String(),
            'amount'           => $parsed_transaction->amount,
            'description'      => $parsed_transaction->getPossibleTransactionDescription(),
            'source_id'        => config('firefly-iii.default_account_id'),
            // 'destination_name' => $parsed_transaction->getFirstSimilarAccountName(),
            'category_id'      => $parsed_transaction->getFirstPossibleCategoryId(),
            'tags'             => ['powered-by-groq'],
            'notes'            => $parsed_transaction->raw_transaction_message ? "Raw transaction message: $parsed_transaction->raw_transaction_message" : null,
        ];

        // Destination name only works if the destination account does not already exist
        if (is_string($destination_account)) {
            $transaction_data['destination_name'] = $destination_account;
        }

        // Destination id only works if the destination account already exists
        if (is_int($destination_account)) {
            $transaction_data['destination_id'] = $destination_account;
        }

        // Handle foreign transactions
        if ($parsed_transaction->isForeignTransaction()) {
            $transaction_data['amount'] = $parsed_transaction->localAmount();
            $transaction_data['foreign_currency_code'] = $parsed_transaction->getCurrency()->value;
            $transaction_data['foreign_amount'] = $parsed_transaction->amount;
        }

        return $this->getJson(
            endpoint: '/transactions',
            method: 'POST',
            params: [
                'transactions' => [$transaction_data],
            ],
        );
    }

    public function accounts(AccountTypes $account_type, bool $get_all = false): array
    {
        $params = [
            'type'  => $account_type->value,
            'limit' => 20,
        ];

        $response = $this->getJson(endpoint: '/accounts', params: $params);

        // Return the first page if $get_all is false
        if (!$get_all) {
            return $response['data'] ?? [];
        }

        $accounts = $response['data'] ?? [];
        $currentPage = $response['meta']['pagination']['current_page'] ?? 1;
        $totalPages = $response['meta']['pagination']['total_pages'] ?? 1;

        // Fetch remaining pages if $get_all is true
        while ($currentPage < $totalPages) {
            $currentPage++;
            $params['page'] = $currentPage;

            $response = $this->getJson(endpoint: '/accounts', params: $params);
            $accounts = array_merge($accounts, $response['data'] ?? []);
        }

        return $accounts;
    }

    public function updateAccount($account_id, array $data): array
    {
        return $this->getJson(
            endpoint: "/accounts/{$account_id}",
            method: 'PUT',
            params: $data,
        );
    }

    public function getTransactionUrl(int|string $transaction_id): string
    {
        return $this->base_url . "/transactions/show/{$transaction_id}";
    }
}
