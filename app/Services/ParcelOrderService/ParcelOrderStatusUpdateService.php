<?php
declare(strict_types=1);

namespace App\Services\ParcelOrderService;

use App\Helpers\ResponseError;
use App\Models\ParcelOrder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\CoreService;
use App\Services\WalletHistoryService\WalletHistoryService;
use App\Traits\Notification;
use DB;
use Exception;
use Log;
use Throwable;

class ParcelOrderStatusUpdateService extends CoreService
{
    use Notification;

    protected function getModelClass(): string
    {
        return ParcelOrder::class;
    }

    /**
     * @param ParcelOrder $model
     * @param string|null $status
     * @param bool $isDelivery
     * @return array
     */
    public function statusUpdate(ParcelOrder $model, ?string $status, bool $isDelivery = false): array
    {
        if ($model->status == $status) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_252,
                'message' => __('errors.' . ResponseError::ERROR_252, locale: $this->language)
            ];
        }

        try {
            $model = DB::transaction(function () use ($model, $status) {

                if ($status == ParcelOrder::STATUS_DELIVERED) {

                    $this->adminWalletTopUp($model);

                }

                if ($status == ParcelOrder::STATUS_CANCELED) {

                    $user  = $model->user;
                    $trxId = $model->transactions->where('status', Transaction::STATUS_PAID)->first()?->id;

                    if (!$user?->wallet && $trxId) {
                        throw new Exception(__('errors.' . ResponseError::ERROR_108, locale: $this->language));
                    }

                    if ($trxId) {

                        (new WalletHistoryService)->create([
                            'type'   => 'topup',
                            'price'  => $model->total_price,
                            'note'   => 'For Order #' . $model->id,
                            'status' => WalletHistory::PAID,
                            'user'   => $user
                        ]);

                    }

                }

                $model->update([
                    'status'  => $status,
                    'current' => in_array($status, [ParcelOrder::STATUS_DELIVERED, ParcelOrder::STATUS_CANCELED]) ? 0 : $model->current,
                ]);

                return $model;
            });
        } catch (Throwable $e) {

            $this->error($e);

            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_501,
                'message' => $e->getMessage()
            ];
        }

        /** @var ParcelOrder $model */

        $this->statusUpdateNotify($model, $isDelivery);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
    }

    /**
     * @param ParcelOrder $model
     * @return void
     * @throws Throwable
     */
    private function adminWalletTopUp(ParcelOrder $model): void
    {
        /** @var User $admin */
        $admin = User::with('wallet')->whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();

        if (!$admin->wallet) {
            Log::error("admin #$admin?->id doesnt have wallet");
            return;
        }

        $request = request()->merge([
            'type'      => 'topup',
            'price'     => $model->total_price,
            'note'      => "For ParcelOrder #$model->id",
            'status'    => WalletHistory::PAID,
            'user'      => $admin,
        ])->all();

        (new WalletHistoryService)->create($request);
    }

}
