<?php

namespace Modules\Payments\Http\Controllers;

use Crater\Http\Controllers\Controller;
use Crater\Models\Payment;
use Crater\Models\Transaction;
use Illuminate\Http\Request;

class PaymentPdfController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Transaction $transaction, Request $request)
    {
        $payment = Payment::where('transaction_id', $transaction->id)->first();

        if (! $transaction->isExpired()) {
            return $payment->getGeneratedPDFOrStream('payment');
        }

        abort(403, 'Link Expired.');
    }
}
