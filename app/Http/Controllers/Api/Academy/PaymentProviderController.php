<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Models\PaymentProvider;
use Illuminate\Http\Request;

class PaymentProviderController extends BaseController
{
    /**
     * List all active payment providers for the authenticated academy.
     */
    public function index(Request $request)
    {
        $providers = PaymentProvider::where('is_active', true)
            ->paginate($request->input('per_page', 15));

        return $this->sendPaginatedResponse($providers, __('message.payment_providers_retrieved'));
    }
}
