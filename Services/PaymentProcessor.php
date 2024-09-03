<?php

namespace Modules\Payments\Services;

use Crater\Models\PaymentMethod;
use Modules\Payments\Services\Paypal\PaymentProvider;
use Modules\Payments\Services\Razorpay\PaymentProvider as RazorpayPaymentProvider;
use Modules\Payments\Services\Stripe\PaymentProvider as StripePaymentProvider;

class PaymentProcessor
{
    private $paymentProvider;

    /**
     * Set payment providers.
     */
    public function __construct()
    {
        $driver = PaymentMethod::find(request()->payment_method_id)->driver;

        switch ($driver) {
            case 'stripe':
                $this->paymentProvider = new StripePaymentProvider();

                break;

            case 'razorpay':
                $this->paymentProvider = new RazorpayPaymentProvider();

                break;

            case 'paypal':
                $this->paymentProvider = new PaymentProvider();

                break;
        }
    }

    public function generatePayment($company, $invoice)
    {
        return $this->paymentProvider->generatePayment($company, $invoice);
    }

    public function confirmTransaction($company, $transaction_id, $request)
    {
        return $this->paymentProvider->confirmTransaction($company, $transaction_id, $request);
    }
}
