<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Helpers\GetShop;
use App\Models\AdsPackage;
use App\Models\Cart;
use App\Models\Currency;
use App\Models\Order;
use App\Models\ParcelOrder;
use App\Models\PaymentProcess;
use App\Models\Payout;
use App\Models\Shop;
use App\Models\ShopAdsPackage;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Repositories\CartRepository\CartRepository;
use App\Services\CoreService;
use App\Services\OrderService\OrderService;
use App\Services\SubscriptionService\SubscriptionService;
use App\Services\TransactionService\TransactionService;
use App\Services\WalletHistoryService\WalletHistoryService;
use Exception;
use Log;
use Throwable;

class BaseService extends CoreService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    public function afterHook($token, $status, ?string $secondToken = null): void
    {
        try {
            $paymentProcess = PaymentProcess::with([
                'model',
                'user',
            ])
                ->where('id', $token)
                ->orWhere('id', $secondToken)
                ->first();

            if (empty($paymentProcess)) {
                return;
            }

            /** @var PaymentProcess $paymentProcess */
            if ($paymentProcess->model_type === Subscription::class) {

                $subscription = $paymentProcess->model;

                $shop = Shop::find(data_get($paymentProcess->data, 'shop_id'));

                $shopSubscription = (new SubscriptionService)->subscriptionAttach(
                    $subscription,
                    (int)$shop?->id,
                    $status === 'paid' ? 1 : 0
                );

                $shopSubscription->fresh(['transaction'])?->transaction?->update([
                    'payment_trx_id' => $token,
                    'status'         => $status,
                ]);

                return;
            }

            if ($paymentProcess->model_type === Wallet::class && $status === Transaction::STATUS_PAID) {

                $totalPrice = (double)data_get($paymentProcess->data, 'total_price') / 100;

                (new WalletHistoryService)->create([
                    'type'              => 'topup',
                    'payment_sys_id'    => data_get($paymentProcess->data, 'payment_id'),
                    'created_by'        => data_get($paymentProcess->data, 'created_by'),
                    'payment_trx_id'    => $token,
                    'price'             => $totalPrice,
                    'note'              => 'Wallet top up',
                    'status'            => WalletHistory::PAID,
                    'user'              => $paymentProcess->user
                ]);

                return;
            }

            if ($paymentProcess->model_type === ShopAdsPackage::class) {

                $time = $paymentProcess->model?->adsPackage?->time ?? 1;
                $type = $paymentProcess->model?->adsPackage?->type ?? 'day';

                $paymentProcess->model->update([
                    'active'     => true,
                    'expired_at' => date('Y-m-d H:i:s', strtotime("+$time $type"))
                ]);

                return;
            }

            if ($paymentProcess->model_type !== Cart::class) {
                $paymentProcess->fresh(['model.transaction']);
            }

            $paymentProcess->model?->transaction?->update([
                'payment_trx_id' => $token,
                'status'         => $status,
            ]);

            if ($paymentProcess->model_type === Cart::class) {

                $paymentProcess->update([
                    'data' => array_merge($paymentProcess->data, ['trx_status' => $status])
                ]);

                if ($status === Transaction::STATUS_PAID) {
                    (new OrderService)->create($paymentProcess->data);
                }

            }

            if ($paymentProcess->model_type === ParcelOrder::class) {
                (new TransactionService)->orderTransaction($paymentProcess->model_id, [
                    'payment_sys_id' => data_get($paymentProcess->data, 'payment_id'),
                    'payment_trx_id' => $paymentProcess->id,
                ], ParcelOrder::class);
            }

        } catch (Throwable $e) {
            Log::error($e->getMessage(), [
                $e->getFile(),
                $e->getLine(),
                $e->getTrace()
            ]);
        }
    }

    /**
     * @param array $data
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function getPayload(array $data, array $payload): array
    {
        $key    = '';
        $before = [];

        if (data_get($data, 'cart_id')) {

            $key = 'cart_id';
            $before = $this->beforeCart($data, $payload);

        } else if (data_get($data, 'parcel_id')) {

            $key = 'parcel_id';
            $before = $this->beforeParcel($data, $payload);

        } else if (data_get($data, 'subscription_id')) {

            $key = 'subscription_id';
            $before = $this->beforeSubscription($data);

        } else if (data_get($data, 'package_id')) {

            $key = 'ads_package_id';
            $before = $this->beforePackage($data, $payload);

        } else if (data_get($data, 'wallet_id')) {

            $key = 'wallet_id';
            $before = $this->beforeWallet($data, $payload);

        }

        return [
            $key,
            $before
        ];
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     * @throws Exception
     */
    public function beforeCart(array $data, array|null $payload): array
    {
        $cart       = Cart::find(data_get($data, 'cart_id'));
        $calculate  = (new CartRepository)->calculateByCartId((int)data_get($data, 'cart_id'), $data);

        if (!data_get($calculate, 'status')) {
            throw new Exception('Cart is empty');
        }

        $totalPrice = ceil(data_get($calculate, 'data.total_price') * 100);

        $cart->update([
            'total_price' => ($totalPrice / $cart->rate) / 100
        ]);

        return [
            'model_type'  => get_class($cart),
            'model_id'    => $cart->id,
            'total_price' => $totalPrice,
            'currency'    => $cart->currency?->title ?? data_get($payload, 'currency'),
            'cart_id'     => $cart->id,
            'user_id'     => auth('sanctum')->id(),
            'status'      => Order::STATUS_NEW,
        ] + $data;
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     */
    public function beforeParcel(array $data, array|null $payload): array
    {
        $parcel     = ParcelOrder::find(data_get($data, 'parcel_id'));
        $totalPrice = ceil($parcel->rate_total_price * 100);

        $parcel->update([
            'total_price' => ($totalPrice / $parcel->rate) / 100
        ]);

        return [
            'model_type'  => get_class($parcel),
            'model_id'    => $parcel->id,
            'total_price' => $totalPrice,
            'currency'    => $parcel->currency?->title ?? data_get($payload, 'currency')
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function beforeSubscription(array $data): array
    {
        $subscription = Subscription::find(data_get($data, 'subscription_id'));
        $totalPrice   = ceil($subscription->price * 100);

        return [
            'model_type'      => get_class($subscription),
            'model_id'        => $subscription->id,
            'currency'        => data_get($data, 'currency'),
            'total_price'     => $totalPrice,
            'shop_id'         => data_get($data, 'shop_id'),
            'subscription_id' => $subscription->id,
        ];
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     */
    public function beforePackage(array $data, array|null $payload): array
    {
        $adsPackage = AdsPackage::find(data_get($data, 'ads_package_id'));
        $totalPrice = ceil($adsPackage->price * 100);

        $model = ShopAdsPackage::updateOrCreate([
            'ads_package_id' => $adsPackage->id,
            'shop_id'        => GetShop::shop()?->id,
            'active'         => false,
        ]);

        $currency = Currency::find($this->currency);

        return [
            'model_type'  => get_class($model),
            'model_id'    => $model->id,
            'total_price' => $totalPrice,
            'currency'    => $currency?->title ?? data_get($payload, 'currency')
        ];
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     */
    public function beforeWallet(array $data, array|null $payload): array
    {
        $model = Wallet::find(data_get($data, 'wallet_id'));

        $totalPrice = ceil((double)data_get($data, 'total_price') * 100);

        $currency = Currency::find($this->currency);

        return [
            'model_type'     => get_class($model),
            'model_id'       => $model->id,
            'total_price'    => $totalPrice,
            'currency'       => $currency?->title ?? data_get($payload, 'currency')
        ];
    }

    public function getValidateData(array $data): array
    {
        $shop     = GetShop::shop();
        $currency = Currency::currenciesList()->where('active', 1)->where('default', 1)->first()?->title;

        if ($shop?->id) {
            $data['shop_id']  = $shop->id;
            $data['currency'] = $currency;
        }

        return $data;
    }

}
