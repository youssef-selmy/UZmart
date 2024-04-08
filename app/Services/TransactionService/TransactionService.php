<?php
declare(strict_types=1);

namespace App\Services\TransactionService;

use App\Helpers\OrderHelper;
use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShopAdsPackage;
use App\Models\ShopSubscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDigitalFile;
use App\Models\Wallet;
use App\Services\CoreService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class TransactionService extends CoreService
{
    protected function getModelClass(): string
    {
        return Transaction::class;
    }

    public function orderTransaction(int $id, array $data, object|string|null $class = Order::class): array
    {
        /** @var Order $order */
        $order = $class::with(['user'])->find($id);

        if (!$order) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ];
        }

        $payment = $this->checkPayment(data_get($data, 'payment_sys_id'), $order, true);

        if (!data_get($payment, 'status')) {
            return $payment;
        }

        if (data_get($payment, 'already_payed')) {
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $order];
        }

        /** @var Transaction $transaction */
        $transaction = $order->createTransaction([
            'price'                 => $order->total_price,
            'user_id'               => $order->user_id,
            'payment_sys_id'        => data_get($data, 'payment_sys_id'),
            'payment_trx_id'        => data_get($data, 'payment_trx_id'),
            'note'                  => $order->id,
            'perform_time'          => now(),
            'status_description'    => 'Transaction for order #' . $order->id
        ]);

        if (data_get($payment, 'wallet')) {
            $this->walletHistoryAdd($order->user, $transaction, $order);
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $order];
    }

    public function walletTransaction(int $id, array $data): array
    {
        $wallet = Wallet::find($id);

        if (empty($wallet)) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ];
        }

        $wallet->createTransaction([
            'price'                 => data_get($data, 'price'),
            'user_id'               => data_get($data, 'user_id'),
            'payment_sys_id'        => data_get($data, 'payment_sys_id'),
            'payment_trx_id'        => data_get($data, 'payment_trx_id'),
            'note'                  => $wallet->id,
            'perform_time'          => now(),
            'status_description'    => "Transaction for wallet #$wallet->id"
        ]);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $wallet];
    }

    public function subscriptionTransaction(int $id, array $data): array
    {
        $subscription = ShopSubscription::find($id);

        if (empty($subscription)) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ];
        } elseif ($subscription->active) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_208,
                'message' => __('errors.' . ResponseError::ERROR_208, locale: $this->language)
            ];
        }

        $request = request()->merge([
            'user_id'     => auth('sanctum')->id(),
            'total_price' => $subscription->price,
        ]);

        $payment = $this->checkPayment(data_get($data, 'payment_sys_id'), $request);

        if (!data_get($payment, 'status')) {
            return $payment;
        }

        $subscription->createTransaction([
            'price'              => $subscription->price,
            'user_id'            => auth('sanctum')->id(),
            'payment_sys_id'     => data_get($data, 'payment_sys_id'),
            'payment_trx_id'     => data_get($data, 'payment_trx_id'),
            'note'               => $subscription->id,
            'perform_time'       => now(),
            'status'             => Transaction::STATUS_PAID,
            'status_description' => "Transaction for Subscription #$subscription->id"
        ]);

        if (data_get($payment, 'wallet')) {

            $subscription->update(['active' => 1]);

            $this->walletHistoryAdd(
                auth('sanctum')->user(),
                $subscription->transaction,
                $subscription,
                'Subscription',
                'withdraw'
            );
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $subscription];
    }

    public function adsTransaction(int $id, array $data): array
    {
        $ads = ShopAdsPackage::with([
            'adsPackage'
        ])
            ->has('adsPackage')
            ->find($id);

        if (empty($ads)) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ];
        }

        /** @var ShopAdsPackage $ads */
        if ($ads->active) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_116,
                'message' => __('errors.' . ResponseError::ERROR_116, locale: $this->language)
            ];
        }

        $request = request()->merge([
            'user_id'     => auth('sanctum')->id(),
            'total_price' => $ads->adsPackage->price,
        ]);

        $payment = $this->checkPayment(data_get($data, 'payment_sys_id'), $request);

        if (!data_get($payment, 'status')) {
            return $payment;
        }

        $ads->createTransaction([
            'price'              => $ads->adsPackage->price,
            'user_id'            => auth('sanctum')->id(),
            'payment_sys_id'     => data_get($data, 'payment_sys_id'),
            'payment_trx_id'     => data_get($data, 'payment_trx_id'),
            'note'               => $ads->id,
            'perform_time'       => now(),
            'status'             => Transaction::STATUS_PAID,
            'status_description' => "Transaction for Ads #$ads->id"
        ]);

        if (data_get($payment, 'wallet')) {

            $ads->update([
                'active' => 1,
            ]);

            $this->walletHistoryAdd(
                auth('sanctum')->user(),
                $ads->transaction,
                $ads,
                'Ads',
                'withdraw'
            );
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $ads];
    }

    private function checkPayment(int $id, Model|Order|Request $model, bool $isOrder = false): array
    {
        $payment = Payment::where('active', 1)->find($id);

        if (!$payment) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ];
        } elseif ($payment->tag !== 'wallet') {
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'payment_tag' => $payment->tag];
        }

        if ($isOrder) {

            /** @var Order $model */
            $changedPrice = data_get($model, 'total_price', 0) - $model->transaction?->price;

            if ($model->transaction?->status === Transaction::STATUS_PAID && $changedPrice <= 1) {
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'already_payed' => true];
            }

            data_set($model, 'total_price', $changedPrice);
        }

        /** @var User $user */
        $user = User::with('wallet')->find(data_get($model, 'user_id'));

        if (empty($user?->wallet)) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_109,
                'message' => __('errors.' . ResponseError::ERROR_109, locale: $this->language)
            ];
        }

        $ratePrice = data_get($model, 'total_price', 0);

        if ($user->wallet->price >= $ratePrice) {

            $user->wallet()->update(['price' => $user->wallet->price - $ratePrice]);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'wallet' => $user->wallet];
        }

        return [
            'status'  => false,
            'code'    => ResponseError::ERROR_109,
            'message' => __('errors.' . ResponseError::ERROR_109, locale: $this->language)
        ];
    }

    private function walletHistoryAdd(
        ?User $user,
        Transaction $transaction,
        Model|Order $model,
        ?string $type = 'Order',
        ?string $paymentType = 'topup'
    ): void
    {
        $modelId = $model->id;

        $user->wallet->histories()->create([
            'uuid'           => Str::uuid(),
            'transaction_id' => $transaction->id,
            'type'           => $paymentType,
            'price'          => $transaction->price,
            'note'           => "Payment $type #$modelId via Wallet" ,
            'status'         => Transaction::STATUS_PAID,
            'created_by'     => $transaction->user_id,
        ]);

        $transaction->update(['status' => Transaction::STATUS_PAID]);

        if ($model instanceof Order) {
            $this->digitalFile($model);
        }

    }

    public function digitalFile(Order $order): void
    {

        $order = $order->fresh([
            'user',
            'orderDetails.stock.product.digitalFile',
        ]);

        foreach ($order->orderDetails as $orderDetail) {

            if (!$orderDetail->stock?->product?->digitalFile?->active) {
                continue;
            }

            UserDigitalFile::updateOrCreate([
                'digital_file_id' => $orderDetail->stock?->product->digitalFile?->id,
                'user_id'         => $order->user_id,
            ], [
                'active'          => true,
            ]);

            OrderHelper::updateStatCount($orderDetail->stock, $orderDetail->quantity);

        }

    }

    /**
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateStatus(int $id, array $data = []): array
    {
        try {
            $transaction = Transaction::with(['payable'])
                ->when(isset($data['shop_id']), function ($q) use ($data) {
                    $q->whereHasMorph('payable', Order::class, fn($q) => $q->where('shop_id', $data['shop_id']));
                })
                ->find($id);

            if (empty($transaction)) {
                return [
                    'status'  => false,
                    'code'    => ResponseError::ERROR_404,
                    'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language),
                ];
            }

            $transaction->update(['status' => $data['status']]);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];
        } catch (Throwable $e) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_400,
                'message' => $e->getMessage(),
            ];
        }
    }
}
