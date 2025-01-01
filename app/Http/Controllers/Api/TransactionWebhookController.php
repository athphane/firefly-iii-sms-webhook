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

        $message = $request->input('message');

        // Check if the message is a OTP message
        if (str($message)->lower()->contains(['one time password', 'otp'])) {
            return response()->json([
                'message' => 'Message received, but will not be processed.'
            ]);
        }

        $transaction = new Transaction(['message' => $request->input('message')]);
        $transaction->save();

        $transaction->process();

        return response()->json([
            'message' => 'Transaction webhook received. Processing.'
        ]);
    }
}
