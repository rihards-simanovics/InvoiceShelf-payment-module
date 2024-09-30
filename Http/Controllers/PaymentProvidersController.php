<?php

namespace Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Modules\Payments\Http\Requests\PaymentMethodRequest;
use Modules\Payments\Http\Resources\PaymentProviderResource;
use Modules\Payments\Traits\AuthorizationTrait;
use Modules\Payments\Helpers\VersionHelper;

if (VersionHelper::checkAppVersion('<', '2.0.0')) {
    VersionHelper::aliasClass('InvoiceShelf\Http\Controllers\Controller', 'App\Http\Controllers\Controller');
    VersionHelper::aliasClass('InvoiceShelf\Models\PaymentMethod', 'App\Models\PaymentMethod');
}

class PaymentProvidersController extends Controller
{
    use AuthorizationTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('owner only');

        $limit = $request->has('limit') ? $request->limit : 5;

        $paymentMethods = PaymentMethod::applyFilters($request->all())
            ->where('type', PaymentMethod::TYPE_MODULE)
            ->whereCompany()
            ->latest()
            ->paginateData($limit);

        return PaymentProviderResource::collection($paymentMethods);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PaymentMethodRequest $request)
    {
        $this->authorize('owner only');

        $data = $request->getPaymentMethodPayload();
        $response = $this->checkAuthorization($data['driver'], $data['settings']['key'], $data['settings']['secret'], $data['use_test_env']);

        if ($response === true) {
            $paymentMethod = PaymentMethod::create($data);

            return new PaymentProviderResource($paymentMethod);
        }

        return respondJson('invalid_credentials', $response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \InvoiceShelf\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentMethod $paymentProvider)
    {
        $this->authorize('owner only');

        return new PaymentProviderResource($paymentProvider);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \InvoiceShelf\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function update(PaymentMethodRequest $request, PaymentMethod $paymentProvider)
    {
        $this->authorize('owner only');

        $data = $request->getPaymentMethodPayload();
        $response = $this->checkAuthorization($data['driver'], $data['settings']['key'], $data['settings']['secret']);

        if ($response === true) {
            $paymentProvider->update($data);

            return new PaymentProviderResource($paymentProvider);
        }

        return respondJson('invalid_credentials', $response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \InvoiceShelf\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentMethod $paymentProvider)
    {
        $this->authorize('owner only');

        $payments = $paymentProvider->payments;

        if ($payments->count() > 0) {
            return respondJson('payments_attached', 'Payments Attached.');
        }

        $paymentProvider->delete();

        return response()->json([
            'success' => 'Payment method deleted successfully',
        ]);
    }
}
