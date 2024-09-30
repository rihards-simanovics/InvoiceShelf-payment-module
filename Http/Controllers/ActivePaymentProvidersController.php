<?php

namespace Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Modules\Payments\Http\Resources\ActivePaymentProviderResource;
use Modules\Payments\Helpers\VersionHelper;

if (VersionHelper::checkAppVersion('<', '2.0.0')) {
    VersionHelper::aliasClass('InvoiceShelf\Http\Controllers\Controller', 'App\Http\Controllers\Controller');
    VersionHelper::aliasClass('InvoiceShelf\Models\Company', 'App\Models\Company');
    VersionHelper::aliasClass('InvoiceShelf\Models\PaymentMethod', 'App\Models\PaymentMethod');
}

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
