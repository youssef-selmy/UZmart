<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\Payout;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Log;
use MercadoPago\Config;
use MercadoPago\Item;
use MercadoPago\Preference;
use MercadoPago\SDK;
use Throwable;

class MercadoPagoService extends BaseService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $data
     * @return PaymentProcess|Model
     * @throws Throwable
     */
    public function processTransaction(array $data): Model|PaymentProcess
    {
        $payment        = Payment::where('tag', 'mercadoPago')->first();

        $paymentPayload = PaymentPayload::where('payment_id', $payment?->id)->first();
        $payload        = $paymentPayload?->payload;

        $order          = Order::find(data_get($data, 'order_id'));
        $totalPrice     = ceil($order->rate_total_price * 2 * 100) / 2;

        $order->update([
            'total_price' => ($totalPrice / $order->rate) / 100
        ]);

        [$key, $before] = $this->getPayload($data, $payload);
        $modelId        = data_get($before, 'model_id');

        $host = request()->getSchemeAndHttpHost();

        $token = data_get($payload, 'token');

        SDK::setAccessToken($token);

        $config = new Config();
        $config->set('sandbox', (bool)data_get($payload, 'sandbox', true));
        $config->set('access_token', $token);

        $trxRef = "$order->id-" . time();

        $item               = new Item;
        $item->id           = $trxRef;
        $item->title        = $order->id;
        $item->quantity     = $order->order_details_sum_quantity;
        $item->unit_price   = $order->order_details_sum_total_price;

        $preference             = new Preference;
        $preference->items      = [$item];
        $preference->back_urls  = [
            'success' => "$host/payment-success?$key=$modelId&lang=$this->language",
            'failure' => "$host/payment-success?$key=$modelId&lang=$this->language",
            'pending' => "$host/payment-success?$key=$modelId&lang=$this->language"
        ];

        $preference->auto_return = 'approved';

        $preference->save();

        $payment_link = $preference->init_point;

        Log::info('$preference', [$preference]);

        if (!$payment_link) {
            throw new Exception('ERROR IN MERCADO PAGO');
        }

        return PaymentProcess::updateOrCreate([
            'user_id'    => auth('sanctum')->id(),
            'model_type' => Order::class,
            'model_id'   => data_get($data, 'order_id'),
        ], [
            'id'    => $trxRef,
            'data'  => array_merge([
                'url'        => $payment_link,
                'payment_id' => $payment->id,
            ], $before)
        ]);
    }

}
