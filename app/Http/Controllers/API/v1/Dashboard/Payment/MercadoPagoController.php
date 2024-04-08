<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\WalletHistory;
use App\Services\PaymentService\MercadoPagoService;
use Illuminate\Http\Request;
use Log;

class MercadoPagoController extends PaymentBaseController
{
    public function __construct(private MercadoPagoService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request): void
    {
        Log::error('mercado pago', [
            'all'   => $request->all(),
            'reAll' => request()->all(),
            'input' => @file_get_contents("php://input")
        ]);

        $status = $request->input('data.status');

        $status = match ($status) {
            'succeeded', 'successful', 'success'                         => WalletHistory::PAID,
            'failed', 'cancelled', 'reversed', 'chargeback', 'disputed'  => WalletHistory::CANCELED,
            default                                                      => 'progress',
        };

        $token = $request->input('data.id');

        $this->service->afterHook($token, $status);
    }

}
