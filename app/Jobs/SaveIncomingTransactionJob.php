<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveIncomingTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $transaction_message)
    {
    }

    private function extract_transaction_info($input): bool|array
    {
        $regex = '/Transaction\s+from\s+\d+\s+on\s+(\d{2}\/\d{2}\/\d{2})\s+at\s+(\d{2}:\d{2}:\d{2})\s+for\s+([A-Za-z]+)(\d+\.\d+)\s+at\s+([A-Za-z\s\d]+)\s+was\s+processed\.\s+Reference\s+No:(\d+),\s+Approval\s+Code:(\d+)/';
        $matches = [];

        if (preg_match($regex, $input, $matches)) {
            return array(
                'transaction_at' => Carbon::parse($matches[1] . ' at ' . $matches[2]),
                'currency'       => $matches[3],
                'amount'         => floatval($matches[4]),
                'vendor'         => trim($matches[5]),
                'reference_no'   => $matches[6],
                'approval_code'  => $matches[7]
            );
        } else {
            return false; // Return false if no match is found
        }
    }

    public function handle(): void
    {
        $data = $this->extract_transaction_info($this->transaction_message);

        if ($data === false) {
            return;
        }

        $vendor = $data['vendor'];

        $vendor = Vendor::query()
            ->where('name', 'like', "%$vendor%")
            ->orWhereRaw("JSON_EXTRACT(aliases, '$[*].name') LIKE '%$vendor%'")
            ->first();

        if (!$vendor) {
            $vendor = Vendor::create([
                'name' => $data['vendor'],
            ]);
        }

        $transaction = new Transaction([
            'message'        => $this->transaction_message,
            'transaction_at' => $data['transaction_at'],
            'vendor'         => $data['vendor'],
            'currency'       => $data['currency'],
            'amount'         => $data['amount'],
            'reference_no'   => $data['reference_no'],
            'approval_code'  => $data['approval_code']
        ]);

        $transaction->vendor()->associate($vendor);
        $transaction->save();

    }
}
