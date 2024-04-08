<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\WalletHistory;
use App\Services\PaymentService\IyzicoService;
use App\Traits\ApiResponse;
use App\Traits\OnResponse;
use Illuminate\Http\Request;
use Log;

class IyzicoController extends PaymentBaseController
{
    use OnResponse, ApiResponse;

    public function __construct(private IyzicoService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request): void
    {
        Log::error('paymentWebHook', $request->all());
        $status = $request->input('data.object.status');

        $status = match ($status) {
            'succeeded' => WalletHistory::PAID,
            default     => 'progress',
        };

        $token = $request->input('data.object.id');

        $this->service->afterHook($token, $status);
    }

}
