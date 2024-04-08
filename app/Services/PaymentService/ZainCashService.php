<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Payout;
use Exception;
use Firebase\JWT\JWT;
use Http;

class ZainCashService extends BaseService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $data
     * @return PaymentProcess
     * @throws Exception
     */
    public function processTransaction(array $data): PaymentProcess
    {
        /** @var Payment $payment */
        $payment = Payment::with([
            'paymentPayload'
        ])
            ->where('tag', 'zain-cash')
            ->first();

        $payload = $payment?->paymentPayload?->payload ?? [];

        [$key, $before] = $this->getPayload($data, $payload);

        $host = request()->getSchemeAndHttpHost();

        $modelId = $before['model_id'];

        $time = time();

        $data = [
            'amount'      => $before['total_price'],
            'serviceType' => 'Order',
            'msisdn'      => $payload['msisdn'] ?? '9647835077893',
            'orderId'     => "{$key}_$modelId",
            'redirectUrl' => "$host/payment-success?$key=$modelId&lang=$this->language",
            'iat'         => $time,
            'exp'         => $time + 60 * 60 * 4
        ];

        $newToken = JWT::encode($data, $payload['key'] ?? '$2y$10$hBbAZo2GfSSvyqAyV2SaqOfYewgYpfR1O19gIh4SqyGWdmySZYPuS' ,'HS256');

        $init = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])
            ->post(($payload['url'] ?? 'https://test.zaincash.iq') . '/transaction/init', [
                'token'      => $newToken,
                'merchantId' => $payload['merchantId'] ?? '5ffacf6612b5777c6d44266f',
                'lang'       => $this->language ?? 'iq'
            ]);

        $errorMessage = $init->json('err.msg');

        if (!empty($errorMessage)) {
            throw new Exception($errorMessage, $init->status());
        }

        $init = $init->json();

        $transactionId = $init['id'];

        $newUrl = ($payload['url'] ?? 'https://test.zaincash.iq') . "/transaction/pay?id=$transactionId";

        return PaymentProcess::updateOrCreate([
            'user_id'    => auth('sanctum')->id(),
            'model_type' => $before['model_type'],
            'model_id'   => $before['model_id'],
        ], [
            'id'   => $transactionId,
            'data' => array_merge(['url' => $newUrl, 'payment_id' => $payment?->id], $before)
        ]);
    }

}
