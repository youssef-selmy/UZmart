<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\PaymentPayload;
use App\Models\Transaction;
use App\Services\PaymentService\MoyasarService;
use Illuminate\Http\Request;
use Log;

class MoyasarController extends PaymentBaseController
{
    public function __construct(private MoyasarService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request): void
    {
        $payload = PaymentPayload::where('tag', 'moya-sar')->first()?->payload;

        if (data_get($payload, 'secret_token') !== $request->input('secret_token')) {
            Log::error('secret_token', $request->all());
            return;
        }

        $status = $request->input('data.status');

        $status = match ($status) {
            'paid', 'captured'      => Transaction::STATUS_PAID,
            'failed'                => Transaction::STATUS_CANCELED,
            'refunded', 'voided'    => Transaction::STATUS_REFUND,
            default                 => 'progress',
        };

        $token = $request->input('data.invoice_id');

        Log::error('paymentWebHook', $request->all());

        $this->service->afterHook($token, $status);
    }

}
