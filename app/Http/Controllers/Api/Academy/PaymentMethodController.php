<?php

namespace App\Http\Controllers\Api\Academy;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Academy\StorePaymentMethodRequest;
use App\Http\Requests\Academy\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends BaseController
{
    /**
     * List all payment methods for the authenticated academy.
     */
    public function index()
    {
        $methods = PaymentMethod::with('provider')
            ->where('academy_id', Auth::guard('academy')->id())
            ->latest()
            ->get();

        return $this->sendResponse($methods, __('message.payment_methods_retrieved'));
    }

    /**
     * Create a new payment method for the authenticated academy.
     */
    public function store(StorePaymentMethodRequest $request)
    {
        $data = $request->validated();
        $data['academy_id'] = Auth::guard('academy')->id();

        $method = PaymentMethod::create($data);

        return $this->sendResponse($method->load('provider'), __('message.payment_method_created'), 201);
    }

    /**
     * Show a single payment method.
     */
    public function show($id)
    {
        $method = PaymentMethod::with('provider')
            ->where('academy_id', Auth::guard('academy')->id())
            ->find($id);

        if (!$method) {
            return $this->sendError(__('message.payment_method_not_found'), 404);
        }

        return $this->sendResponse($method, __('message.payment_method_retrieved'));
    }

    /**
     * Update a payment method.
     */
    public function update(UpdatePaymentMethodRequest $request, $id)
    {
        $method = PaymentMethod::where('academy_id', Auth::guard('academy')->id())->find($id);

        if (!$method) {
            return $this->sendError(__('message.payment_method_not_found'), 404);
        }

        $method->update($request->validated());

        return $this->sendResponse($method->fresh()->load('provider'), __('message.payment_method_updated'));
    }

    /**
     * Delete a payment method.
     */
    public function destroy($id)
    {
        $method = PaymentMethod::where('academy_id', Auth::guard('academy')->id())->find($id);

        if (!$method) {
            return $this->sendError(__('message.payment_method_not_found'), 404);
        }

        $method->delete();

        return $this->sendResponse(null, __('message.payment_method_deleted'));
    }
}
