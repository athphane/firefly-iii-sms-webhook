<?php

namespace App\Support\FireflyIII\Enums;

enum TransactionTypes: string
{
    case WITHDRAWAL = 'withdrawal';
    case DEPOSIT = 'deposit';
    case TRANSFER = 'transfer';
    case RECONCILIATION = 'reconciliation';
    case OPENING_BALANCE = 'opening_balance';
}
