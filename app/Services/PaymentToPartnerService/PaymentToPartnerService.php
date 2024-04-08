<?php

namespace App\Services\PaymentToPartnerService;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentToPartner;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\CoreService;
use App\Services\WalletHistoryService\WalletHistoryService;
use DB;
use Throwable;

class PaymentToPartnerService extends CoreService
{
    protected function getModelClass(): string
    {
        return PaymentToPartner::class;
    }

    public function createMany(array $data): array
    {
        $payment = Payment::find(data_get($data, 'payment_id'));
        $type    = data_get($data, 'type');

        if (empty($payment) || !in_array($payment->tag, ['wallet', 'cash'])) {
            return [
                'status'   => false,
                'code'     => ResponseError::ERROR_434,
                'message'  => __('errors.' . ResponseError::ERROR_434, locale: $this->language)
            ];
        }

        $orders = Order::with([
            'coupon',
            'pointHistories',
            'shop.seller.wallet',
            'deliveryman.wallet'
        ])
            ->find(data_get($data, 'data', []));

        $errors = [];

        foreach ($orders as $order) {

            try {
                DB::transaction(function () use ($order, $payment, $type, &$errors) {

                    /** @var Order $order */
                    $seller		 = $order->shop?->seller;
                    $deliveryman = $order->deliveryman;

                    if ($type === PaymentToPartner::SELLER) {

                        $this->setError($seller, $order, $payment, $errors);

                        if (!empty($seller)) {
                            $this->addForSeller($order, $seller, $payment);
                        }

                    }

                    if ($type === PaymentToPartner::DELIVERYMAN) {

                        $this->setError($deliveryman, $order, $payment, $errors);

                        if (!empty($deliveryman)) {
                            $this->addForDeliveryman($order, $deliveryman, $payment);
                        }

                    }

                });
            } catch (Throwable $e) {
                $errors[] = [
                    'message' 	=> $e->getMessage()
                ];
            }

        }

        return count($errors) === 0 ? [
            'status'  => true,
            'code'    => ResponseError::NO_ERROR,
            'message' => __('errors.' . ResponseError::NO_ERROR, locale: $this->language)
        ] : [
            'status'  => false,
            'code'    => ResponseError::ERROR_422,
            'message' => __('errors.' . ResponseError::ERROR_422, locale: $this->language),
            'params'  => $errors
        ];
    }

    /**
     * @param Order $order
     * @param User $seller
     * @param Payment $payment
     * @return void
     * @throws Throwable
     */
    private function addForSeller(Order $order, User $seller, Payment $payment): void
    {
        $sellerPrice = $order->total_price
            - $order->delivery_fee
            - $order->service_fee
            - $order->commission_fee
            - $order->coupon_price;

        if ($payment->tag === 'wallet') {

            DB::transaction(function () use ($sellerPrice, $order, $seller) {
                (new WalletHistoryService)->create([
                    'type'  	=> $sellerPrice > 0 ? 'topup' : 'withdraw',
                    'price' 	=> (double)str_replace('-', '', (string)$sellerPrice),
                    'note'  	=> "For Seller Order payment #$order->id",
                    'status'	=> WalletHistory::PAID,
                    'user'  	=> $seller,
                ]);

                (new WalletHistoryService)->create([
                    'type'  	=> $sellerPrice > 0 ? 'withdraw' : 'topup',
                    'price' 	=> (double)str_replace('-', '', (string)$sellerPrice),
                    'note'  	=> "Payment for Seller. Order #$order->id",
                    'status'	=> WalletHistory::PAID,
                    'user'  	=> auth('sanctum')->user(),
                ]);
            });

        }

        $sellerPartner = PaymentToPartner::create([
            'user_id'   => $seller->id,
            'order_id'  => $order->id,
            'type'		=> PaymentToPartner::SELLER,
        ]);

        $sellerPartner->createTransaction([
            'price'             	=> $sellerPrice,
            'user_id'           	=> $seller->id,
            'payment_sys_id'    	=> $payment->id,
            'note'              	=> 'Transaction for seller payment to #' . $order->id,
            'perform_time'      	=> now(),
            'status'            	=> Transaction::STATUS_PAID,
            'status_description'	=> 'Transaction for seller payment to #' . $order->id
        ]);

    }

    /**
     * @throws Throwable
     */
    private function addForDeliveryman(Order $order, User $deliveryman, Payment $payment): void
    {

        if ($payment->tag === 'wallet') {

            DB::transaction(function () use ($order, $deliveryman) {
                (new WalletHistoryService)->create([
                    'type'  	=> $order->delivery_fee ? 'topup' : 'withdraw',
                    'price' 	=> (double)str_replace('-', '', (string)$order->delivery_fee),
                    'note'  	=> "For Deliveryman Order payment #$order->id",
                    'status'	=> WalletHistory::PAID,
                    'user'  	=> $deliveryman,
                ]);

                (new WalletHistoryService)->create([
                    'type'  	=> $order->delivery_fee ? 'withdraw' : 'topup',
                    'price' 	=> (double)str_replace('-', '', (string)$order->delivery_fee),
                    'note'  	=> "Payment for Deliveryman. Order #$order->id",
                    'status'	=> WalletHistory::PAID,
                    'user'  	=> auth('sanctum')->user(),
                ]);
            });

        }

        $deliveryManPartner = PaymentToPartner::create([
            'user_id'  	=> $deliveryman->id,
            'order_id' 	=> $order->id,
            'type'		=> PaymentToPartner::DELIVERYMAN,
        ]);

        $deliveryManPartner->createTransaction([
            'price'                 => $order->delivery_fee,
            'user_id'               => $deliveryman->id,
            'payment_sys_id'        => $payment->id,
            'note'                  => 'Transaction for deliveryman payment to #' . $order->id,
            'perform_time'          => now(),
            'status'                => Transaction::STATUS_PAID,
            'status_description'    => 'Transaction for deliveryman payment to #' . $order->id
        ]);

    }

    public function setError(?User $model, Order $order, Payment $payment, array &$errors = []) {

        if (empty($model)) {
            $errors[] = [
                'order_id' 	=> $order->id,
                'user' 		=> $model,
                'message' 	=> __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ];
        }

        if ($payment->tag === 'wallet' && !$model->wallet) {
            $errors[] = [
                'order_id' 	=> $order->id,
                'user' 		=> $model,
                'message' 	=> __('errors.' . ResponseError::ERROR_108, locale: $this->language)
            ];
        }

    }
}
