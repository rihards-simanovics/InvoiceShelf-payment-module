<?php

namespace Modules\Payments\Http\Controllers;

use InvoiceShelf\Http\Controllers\Controller;
use InvoiceShelf\Models\Company;
use InvoiceShelf\Models\PaymentMethod;
use Illuminate\Http\Request;
use Modules\Payments\Http\Resources\ActivePaymentProviderResource;

class ActivePaymentProvidersController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Company $company)
    {
        $activeProviders = PaymentMethod::whereCompanyId($company->id)
            ->where('type', PaymentMethod::TYPE_MODULE)
            ->when($request->has('driver'), function ($query) use ($request) {
                return $query->where('driver', $request->driver);
            })
            ->where('active', true)
            ->get();

        return ActivePaymentProviderResource::collection($activeProviders);
    }
}
