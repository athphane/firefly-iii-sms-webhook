<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SaveIncomingTransactionJob;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionWebhookController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $transaction = new Transaction(['message' => $request->input('message')]);
        $transaction->save();

        $transaction->process();

        return response()->json([
            'message' => 'Transaction webhook received. Processing.'
        ]);
    }
}
