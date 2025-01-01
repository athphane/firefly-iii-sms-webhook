<?php

namespace App\Support\FireflyIII\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array about()
 * @method static array aboutUser()
 * @method static array transactions()
 * @method static array getAccountsAutocomplete(string $query)
 * @method static array getTransactionsFromAccount(string $account_id)
 * @method static array createTransaction(\App\Support\FireflyIII\Entities\ParsedTransactionMessage $parsed_transaction)
 * @method static array accounts(\App\Support\FireflyIII\Enums\AccountTypes $account_type, bool $get_all = false)
 * @method static array updateAccount(void $account_id, array $data)
 *
 * @see \App\Support\FireflyIII\FireflyIII
 */
class FireflyIII extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'firefly-iii';
    }
}
