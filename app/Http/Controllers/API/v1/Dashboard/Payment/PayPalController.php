<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\WalletHistory;
use App\Services\PaymentService\PayPalService;
use Illuminate\Http\Request;

class PayPalController extends PaymentBaseController
{
    public function __construct(private PayPalService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request): void
    {
        $status = $request->input('resource.status');

        $status = match ($status) {
            'APPROVED', 'COMPLETED', 'CAPTURED' => WalletHistory::PAID,
            'VOIDED'     => WalletHistory::CANCELED,
            default     => 'progress',
        };

        $token = $request->input('data.object.id');

        $this->service->afterHook($token, $status);
    }

}
