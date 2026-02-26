<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Models\PaymentProvider;

class PaymentProviderController extends BaseController
{
    /**
     * List all active payment providers for the authenticated academy.
     */
    public function index()
    {
        $providers = PaymentProvider::where('is_active', true)->get();

        return $this->sendResponse($providers, __('message.payment_providers_retrieved'));
    }
}
