<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\WalletHistory;
use App\Services\PaymentService\FlutterWaveService;
use Illuminate\Http\Request;
use Log;

class FlutterWaveController extends PaymentBaseController
{
    public function __construct(private FlutterWaveService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request): void
    {
        $status = $request->input('data.status');

        $status = match ($status) {
            'succeeded', 'success'  => WalletHistory::PAID,
            default                 => 'progress',
        };

        $token = $request->input('data.id');

        Log::error('flutterWare', $request->all());

        $this->service->afterHook($token, $status);
    }

}
