<?php

namespace Modules\Payments\Http\Controllers;

use Crater\Http\Controllers\Controller;
use Crater\Models\Company;
use Crater\Models\Invoice;
use Illuminate\Http\Request;
use Modules\Payments\Services\PaymentProcessor;

class PaymentController extends Controller
{
    public function generatePayment(Company $company, Invoice $invoice, PaymentProcessor $processor, Request $request)
    {
        if ($invoice->paid_status == Invoice::STATUS_PAID && $invoice->status == Invoice::STATUS_COMPLETED) {
            return respondJson('invoice_paid.', 'Invoice Paid.');
        }

        $response = $processor->generatePayment($company, $invoice);

        return $response;
    }

    public function confirmTransaction(Company $company, $transaction_id, PaymentProcessor $processor, Request $request)
    {
        return $processor->confirmTransaction($company, $transaction_id, $request);
    }
}
