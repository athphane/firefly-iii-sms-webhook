<?php

namespace App\Jobs;

use App\Actions\TransactionProcessor;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Transaction $transaction)
    {
    }

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        /** @var TransactionProcessor $processor */
        $processor = app(TransactionProcessor::class);

        $processor->handle($this->transaction);
    }
}
