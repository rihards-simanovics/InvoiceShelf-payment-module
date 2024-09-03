<?php

namespace Modules\Payments\Services;

use Crater\Models\Company;

interface PaymentInterface
{
    public function generatePayment(Company $company, $invoice);

    public function confirmTransaction(Company $company, $transaction_id, $request);
}
