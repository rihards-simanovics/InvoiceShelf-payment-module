<?php

namespace Modules\Payments\Services;

use App\Models\Company;
use Modules\Payments\Helpers\VersionHelper;

if (VersionHelper::checkAppVersion('<', '2.0.0')) {
    VersionHelper::aliasClass('InvoiceShelf\Models\Company', 'App\Models\Company');
}

interface PaymentInterface
{
    public function generatePayment(Company $company, $invoice);

    public function confirmTransaction(Company $company, $transaction_id, $request);
}
