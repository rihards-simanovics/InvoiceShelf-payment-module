<?php

namespace Modules\Payments\Http\Controllers;

use Crater\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentDriversController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return response()->json(config('payment'));
    }
}
