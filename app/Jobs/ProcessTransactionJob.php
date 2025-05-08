<?php

namespace App\Jobs;

use App\Actions\TransactionProcessor;
use App\Models\Transaction;
use App\Notifications\SendTransactionCreatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class ProcessTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Transaction $transaction,
        public bool $notify = false,
    )
    {
    }

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        /** @var TransactionProcessor $processor */
        $processor = app(TransactionProcessor::class);

        $transaction = $processor->handle($this->transaction);

        if ($this->notify) {
            Notification::route('telegram', config('telegram.admin_user_id'))
                ->notifyNow(new SendTransactionCreatedNotification($transaction));
        }
    }
}
